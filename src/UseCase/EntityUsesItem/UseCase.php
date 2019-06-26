<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityUsesItem;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\Variety;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $actorId,
        UuidInterface $itemVarietyId,
        UuidInterface $actionId
    ): Result {
        $game = $this->gameRepository->find($gameId);
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $itemVariety = $this->varietyRepository->find($itemVarietyId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($entity)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($itemVariety)) {
            return Result::varietyNotFound($itemVarietyId);
        }

        if (!$actor->hasInventory()) {
            return Result::failed("{$actor->getLabel()} has no inventory.");
        }

        if (!$actor->getInventory()->containsItem($itemVarietyId)) {
            return Result::failed("{$actor->getLabel()} does not have {$itemVariety->getLabel()}.");
        }

        if ($actionId->equals(Uuid::fromString(ActionRepositoryConfig::CONSUME))) {
            $this->consume($entity, $actor, $itemVariety);

        } elseif ($actionId->equals(Uuid::fromString(ActionRepositoryConfig::PLACE))) {
            $this->place($game, $entity, $itemVariety);
        }

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }

    private function consume(Entity $entity, Entity $actor, Variety $itemVariety): void
    {
        $entity->consumeItem($actor->takeItem($itemVariety->getId()));

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($entity);
        $unitOfWork->save($actor);
        $unitOfWork->commit($this->unitOfWorkProcessor);
    }

    private function place(Game $game, Entity $actingEntity, Variety $itemVariety): void
    {
        $actingEntity->getInventory()->decrementItemQuantity($itemVariety->getId(), 1);

        $placedEntity = new Entity(
            $placedEntityId = Uuid::uuid4(),
            $game->getId(),
            $itemVariety->getId(),
            $itemVariety->getLabel(),
            $itemVariety->getIcon(),
            $orderIndex = 1,
            true,
            Construction::constructed(),
            [],
            Inventory::empty($placedEntityId, $itemVariety)
        );

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($actingEntity);
        $unitOfWork->save($placedEntity);
        $unitOfWork->save($game);
        $unitOfWork->commit($this->unitOfWorkProcessor);
    }
}
