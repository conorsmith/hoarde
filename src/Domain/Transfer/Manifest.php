<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain\Transfer;

use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Manifest
{
    /** @var UuidInterface */
    private $entityId;

    /** @var array */
    private $items;

    public function __construct(UuidInterface $entityId, iterable $items)
    {
        $this->entityId = $entityId;
        $this->items = [];

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->items[] = $item;
        }
    }

    public function getEntityId(): UuidInterface
    {
        return $this->entityId;
    }

    public function transferItems(
        Inventory $inventoryFrom,
        Inventory $inventoryTo,
        VarietyRepository $varietyRepository
    ): void {
        foreach ($this->items as $manifestItem) {
            if (!$manifestItem->isEmpty()) {

                $inventoryFrom->decrementItemQuantity(
                    $manifestItem->getVarietyId(),
                    $manifestItem->getQuantity()
                );

                $inventoryTo->incrementItemQuantity(
                    $manifestItem->getVarietyId(),
                    $manifestItem->getQuantity(),
                    $varietyRepository
                );

            }
        }
    }
}
