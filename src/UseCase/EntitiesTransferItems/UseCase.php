<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntitiesTransferItems;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\Transfer\Manifest;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use DomainException;
use Ramsey\Uuid\Uuid;
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

    public function __invoke(UuidInterface $gameId, Manifest $manifestAToB, Manifest $manifestBToA): Result
    {
        $entityA = $this->entityRepository->findInGame($manifestAToB->getEntityId(), $gameId);
        $entityB = $this->entityRepository->findInGame($manifestBToA->getEntityId(), $gameId);

        if (is_null($entityA)) {
            return Result::entityNotFound($manifestAToB->getEntityId(), $gameId);
        }

        if (is_null($entityB)) {
            return Result::entityNotFound($manifestBToA->getEntityId(), $gameId);
        }

        if (!$entityA->hasInventory()) {
            return Result::entityHasNoInventory($entityA);
        }

        if (!$entityB->hasInventory()) {
            return Result::entityHasNoInventory($entityB);
        }

        $inventoryA = $entityA->getInventory();
        $inventoryB = $entityB->getInventory();

        $transientInventory = Inventory::empty(
            Uuid::uuid4(),
            $this->varietyRepository->find($entityA->getVarietyId())
        );

        try {
            $manifestAToB->transferItems(
                $inventoryA,
                $transientInventory,
                $this->varietyRepository
            );
            $manifestBToA->transferItems(
                $inventoryB,
                $inventoryA,
                $this->varietyRepository
            );
            $manifestAToB->transferItems(
                $transientInventory,
                $inventoryB,
                $this->varietyRepository
            );

        } catch (DomainException $e) {
            return Result::failed($e->getMessage());
        }

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($entityA);
        $unitOfWork->save($entityB);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
