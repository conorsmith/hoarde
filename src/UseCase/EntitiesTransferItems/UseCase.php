<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntitiesTransferItems;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Inventory;
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

    public function __invoke(UuidInterface $gameId, array $manifests): Result
    {
        if (count($manifests) !== 2) {
            return Result::failed("A transfer requires exactly two manifests");
        }

        $manifestA = $manifests[0];
        $manifestB = $manifests[1];

        $entityAId = Uuid::fromString($manifestA['entityId']);
        $entityBId = Uuid::fromString($manifestB['entityId']);

        $entityA = $this->entityRepository->findInGame($entityAId, $gameId);
        $entityB = $this->entityRepository->findInGame($entityBId, $gameId);

        if (is_null($entityA)) {
            return Result::entityNotFound($entityAId, $gameId);
        }

        if (is_null($entityB)) {
            return Result::entityNotFound($entityBId, $gameId);
        }

        if (!$entityA->hasInventory()) {
            return Result::entityHasNoInventory($entityA);
        }

        if (!$entityB->hasInventory()) {
            return Result::entityHasNoInventory($entityB);
        }

        $inventoryA = $entityA->getInventory();
        $inventoryB = $entityB->getInventory();

        $transientInventory = new Inventory(
            Uuid::uuid4(),
            $inventoryA->getCapacity(),
            [],
            []
        );

        try {
            foreach ($manifestA['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $inventoryA->decrementItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $transientInventory->incrementItemQuantity(
                        Uuid::fromString($item['varietyId']),
                        intval($item['quantity']),
                        $this->varietyRepository
                    );
                }
            }

            foreach ($manifestB['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $inventoryB->decrementItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $inventoryA->incrementItemQuantity(
                        Uuid::fromString($item['varietyId']),
                        intval($item['quantity']),
                        $this->varietyRepository
                    );
                }
            }

            foreach ($transientInventory->getItems() as $item) {
                $transientInventory->decrementItemQuantity($item->getVariety()->getId(), $item->getQuantity());
                $inventoryB->incrementItemQuantity(
                    $item->getVariety()->getId(),
                    $item->getQuantity(),
                    $this->varietyRepository
                );
            }
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
