<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Inventory
{
    /** @var UuidInterface */
    private $entityId;

    /** @var Item[] */
    private $items;

    public function __construct(UuidInterface $entityId, iterable $items)
    {
        $this->entityId = $entityId;
        $this->items = [];

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->items[strval($item->getVariety()->getId())] = $item;
        }
    }

    public function getItems(): iterable
    {
        return $this->items;
    }

    public function containsItem(UuidInterface $varietyId): bool
    {
        return array_key_exists(strval($varietyId), $this->items);
    }

    public function getItem(UuidInterface $varietyId): Item
    {
        if (!$this->containsItem($varietyId)) {
            throw new DomainException;
        }

        return $this->items[strval($varietyId)];
    }

    public function containsItemAmountingToAtLeast(UuidInterface $varietyId, int $minimumQuantity): bool
    {
        if (!$this->containsItem($varietyId)) {
            return false;
        }

        return $this->items[strval($varietyId)]->getQuantity() >= $minimumQuantity;
    }

    public function addItem(Item $addedItem): void
    {
        $varietyId = $addedItem->getVariety()->getId();

        if ($this->containsItem($varietyId)) {
            $this->getItem($varietyId)
                ->add($addedItem->getQuantity());
            return;
        }

        $this->items[strval($varietyId)] = $addedItem;
    }

    public function discardItem(UuidInterface $varietyId, int $quantityDiscarded): void
    {
        if (!array_key_exists(strval($varietyId), $this->items)) {
            throw new DomainException;
        }

        $item = $this->items[strval($varietyId)];

        if ($item->getQuantity() < $quantityDiscarded) {
            throw new DomainException;
        }

        if ($item->getQuantity() === $quantityDiscarded) {
            unset($this->items[strval($varietyId)]);
            return;
        }

        $item->decrementBy($quantityDiscarded);
    }

    public function reduceItemQuantityTo(UuidInterface $varietyId, int $newQuantity): void
    {
        if ($newQuantity === 0) {
            unset($this->items[strval($varietyId)]);
            return;
        }

        $this->items[strval($varietyId)]->reduceTo($newQuantity);
    }

    public function incrementItemQuantity(
        UuidInterface $varietyId,
        int $increment,
        VarietyRepository $varietyRepository
    ): void {
        if ($this->containsItem($varietyId)) {
            $this->items[strval($varietyId)]->incrementBy($increment);
            return;
        }

        $this->items[strval($varietyId)] = $varietyRepository
            ->find($varietyId)
            ->createItemWithQuantity($increment);
    }

    public function decrementItemQuantity(UuidInterface $varietyId, int $decrement): void
    {
        if (!$this->containsItem($varietyId)) {
            throw new DomainException;
        }

        $item = $this->getItem($varietyId);

        if ($item->lessThan($decrement)) {
            throw new DomainException;
        }

        if ($item->moreThan($decrement)) {
            $item->decrementBy($decrement);
            return;
        }

        unset($this->items[strval($varietyId)]);
    }

    public function removeQuantityFromItem(Item $item, int $quantity): void
    {
        if ($item->lessThan($quantity)) {
            throw new DomainException;
        }

        if ($item->moreThan($quantity)) {
            $item->remove($quantity);
            return;
        }

        unset($this->items[strval($item->getVariety()->getId())]);
    }
}
