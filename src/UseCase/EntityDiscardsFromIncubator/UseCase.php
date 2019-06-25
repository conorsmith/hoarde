<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityDiscardsFromIncubator;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $varietyId,
        int $remainingSteps,
        int $quantityDiscarded
    ): Result {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $variety = $this->varietyRepository->find($varietyId);

        if (is_null($entity)) {
            return Result::failed("Entity {$entityId} was not found in game {$gameId}.");
        }

        if (is_null($variety)) {
            return Result::failed("Variety {$varietyId} was not found.");
        }

        if (!$entity->hasInventory()) {
            return Result::failed("Entity {$entityId} has no inventory.");
        }

        $inventory = $entity->getInventory();

        $inventoryEntities = $inventory->getEntities();
        $qualifyingEntities = [];

        /** @var $inventoryEntities Entity[] */
        foreach ($inventoryEntities as $inventoryEntity) {
            if ($inventoryEntity->getVarietyId()->equals($variety->getId())
                && $inventoryEntity->getConstruction()->getRemainingSteps() === $remainingSteps
            ) {
                $qualifyingEntities[] = $inventoryEntity;
            }
        }

        if (count($qualifyingEntities) < $quantityDiscarded) {
            return Result::failed(
                "{$entity->getLabel()} does not have {$variety->getLabel()} ({$quantityDiscarded})"
                . " with {$remainingSteps} remaining turns."
            );
        }

        $discardedEntities = [];

        for ($i = 0; $i < $quantityDiscarded; $i++) {
            $discardedEntity = array_shift($qualifyingEntities);
            $inventory->discardEntity($discardedEntity);
            $discardedEntities[] = $discardedEntity;
        }

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($entity);
        foreach ($discardedEntities as $discardedEntity) {
            $unitOfWork->delete($discardedEntity);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded(
            "{$variety->getLabel()} ({$quantityDiscarded}) with {$remainingSteps} remaining turns"
            . " discarded from {$entity->getLabel()}."
        );
    }
}
