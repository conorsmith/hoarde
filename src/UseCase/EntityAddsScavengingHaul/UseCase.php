<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityAddsScavengingHaul;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\ScavengingHaulRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var ScavengingHaulRepository */
    private $scavengingHaulRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        EntityRepository $entityRepository,
        ScavengingHaulRepository $scavengingHaulRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->entityRepository = $entityRepository;
        $this->scavengingHaulRepository = $scavengingHaulRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $haulId,
        iterable $selectedItems,
        iterable $modifiedInventory
    ): Result {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $haul = $this->scavengingHaulRepository->find($haulId);

        if (is_null($entity)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (is_null($haul)) {
            return Result::failed("Scavenging Haul {$haulId} was not found.");
        }

        foreach ($selectedItems as $varietyId => $quantity) {
            $haul->reduceItemQuantity(Uuid::fromString($varietyId), $quantity);
        }

        $inventory = $entity->getInventory();

        foreach ($modifiedInventory as $varietyId => $quantity) {
            $inventory->reduceItemQuantityTo(Uuid::fromString($varietyId), $quantity);
        }

        if (!$haul->isRetrievableBy($entity)) {
            return Result::failed("{$entity->getLabel()} cannot carry that much.");
        }

        foreach ($haul->getItems() as $item) {
            $inventory->addItem($item);
        }

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($entity);
        $unitOfWork->delete($haul);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
