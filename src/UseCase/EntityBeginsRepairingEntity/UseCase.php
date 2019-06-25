<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityBeginsRepairingEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $targetId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $target = $this->entityRepository->findInGame($targetId, $gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($target)) {
            return Result::entityNotFound($targetId, $gameId);
        }

        $targetVariety = $this->varietyRepository->find($target->getVarietyId());

        if (!$targetVariety->hasBlueprint()) {
            return Result::failed("{$targetVariety->getLabel()} cannot be repaired.");
        }

        $otherEntities = $this->findOtherEntitiesInTheVicinity($actorId, $gameId);
        $otherInventories = $this->mapInventories($otherEntities);

        $blueprint = $targetVariety->getBlueprint();
        $actorInventory = $actor->getInventory();

        if (!$blueprint->canBeginConstruction($actorInventory, $otherInventories)) {
            return Result::failed("Repair requirements not met.");
        }

        $blueprint->discardUsedMaterials($actorInventory, $otherInventories);

        $target->repair();
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
