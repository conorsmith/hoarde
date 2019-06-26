<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityBeginsConstructingEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $targetVarietyId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $targetVariety = $this->varietyRepository->find($targetVarietyId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($targetVariety)) {
            return Result::varietyNotFound($targetVarietyId);
        }

        if (!$targetVariety->hasBlueprint()) {
            return Result::failed("{$targetVariety->getLabel()} cannot be constructed.");
        }

        $otherEntities = $this->findOtherEntitiesInTheVicinity($actorId, $gameId);
        $otherInventories = $this->mapInventories($otherEntities);

        $blueprint = $targetVariety->getBlueprint();
        $actorInventory = $actor->getInventory();

        if (!$blueprint->canBeginConstruction($actorInventory, $otherInventories)) {
            return Result::failed("Construction requirements not met.");
        }

        $blueprint->discardUsedMaterials($actorInventory, $otherInventories);

        $target = new Entity(
            $targetId = Uuid::uuid4(),
            $game->getId(),
            $targetVariety->getId(),
            $targetVariety->getLabel(),
            $targetVariety->getIcon(),
            $orderIndex = 1,
            true,
            new Construction(false, $blueprint->getTurns() - 1, $blueprint->getTurns()),
            [],
            Inventory::empty($targetId, $targetVariety)
        );

        $actor->wait();

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($game);
        $unitOfWork->save($actor);
        $unitOfWork->save($target);
        foreach ($otherEntities as $otherEntity) {
            $unitOfWork->save($otherEntity);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$actor->isIntact()) {
            return Result::actorExpired($actor);
        }

        return Result::succeeded();
    }

    private function findOtherEntitiesInTheVicinity(UuidInterface $actorId, UuidInterface $gameId): iterable
    {
        $entityIds = $this->gameRepository->findEntityIds($gameId);
        $entities = [];

        foreach ($entityIds as $entityId) {
            if (!$entityId->equals($actorId)) {
                $entities[] = $this->entityRepository->find($entityId);
            }
        }

        return $entities;
    }

    private function mapInventories(iterable $entities): iterable
    {
        $inventories = [];

        foreach ($entities as $entity) {
            if ($entity->hasInventory()) {
                $inventories[] = $entity->getInventory();
            }
        }

        return $inventories;
    }
}
