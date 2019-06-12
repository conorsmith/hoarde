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
        UuidInterface $itemVarietyId,
        UuidInterface $actionId
    ): Result {
        $game = $this->gameRepository->find($gameId);
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $itemVariety = $this->varietyRepository->find($itemVarietyId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($entity)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (is_null($itemVariety)) {
            return Result::failed("Variety {$itemVarietyId} was not found.");
        }

        if (!$entity->hasInventory()) {
            return Result::failed("Entity {$entityId} has no inventory.");
        }

        if (!$entity->getInventory()->containsItem($itemVarietyId)) {
            return Result::failed("{$entity->getLabel()} does not have {$itemVariety->getLabel()}.");
        }

        if ($actionId->equals(Uuid::fromString(ActionRepositoryConfig::CONSUME))) {
            $this->consume($entity, $itemVariety);

        } elseif ($actionId->equals(Uuid::fromString(ActionRepositoryConfig::PLACE))) {
            $this->place($game, $entity, $itemVariety);
        }

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }

    private function consume(Entity $entity, Variety $itemVariety): void
    {
        $entity->consumeItem($itemVariety->getId());

        $this->entityRepository->save($entity);
    }

    private function place(Game $game, Entity $actingEntity, Variety $itemVariety): void
    {
        $actingEntity->getInventory()->decrementItemQuantity($itemVariety->getId(), 1);

        $placedEntity = new Entity(
            Uuid::uuid4(),
            $game->getId(),
            $itemVariety->getId(),
            $itemVariety->getLabel(),
            $itemVariety->getIcon(),
            true,
            Construction::constructed(),
            [],
            []
        );

        $game->proceedToNextTurn();

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($actingEntity);
        $unitOfWork->save($placedEntity);
        $unitOfWork->save($game);
        $unitOfWork->commit($this->unitOfWorkProcessor);
    }
}
