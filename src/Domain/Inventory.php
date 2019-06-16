<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Inventory
{
    public static function empty(UuidInterface $entityId, Variety $variety): self
    {
        return new self(
            $entityId,
            $variety->getInventoryCapacity(),
            [],
            []
        );
    }

    /** @var UuidInterface */
    private $entityId;

    /** @var int */
    private $capacity;

    /** @var Item[] */
    private $items;

    /** @var Entity[] */
    private $entities;

    public function __construct(UuidInterface $entityId, int $capacity, iterable $items, iterable $entities)
    {
        $this->entityId = $entityId;
        $this->capacity = $capacity;
        $this->items = [];
        $this->entities = [];

        foreach ($items as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->items[strval($item->getVariety()->getId())] = $item;
        }

        foreach ($entities as $entity) {
            if (!$entity instanceof Entity) {
                throw new DomainException;
            }

            $this->entities[strval($entity->getId())] = $entity;
        }
    }

    public function getCapacity(): int
    {
        return $this->capacity;
    }

    public function getWeight(): int
    {
        $weight = 0;

        foreach ($this->items as $item) {
            $weight += $item->getWeight();
        }

        return $weight;
    }

    public function getItems(): iterable
    {
        return $this->items;
    }

    public function getEntities(): iterable
    {
        return $this->entities;
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

    public function addEntity(Entity $addedEntity): void
    {
        if (array_key_exists(strval($addedEntity->getId()), $this->entities)) {
            throw new DomainException;
        }

        if ($this->entityId->equals($addedEntity->getId())) {
            throw new DomainException;
        }

        $this->entities[strval($addedEntity->getId())] = $addedEntity;
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

            if ($this->getWeight() > $this->getCapacity()) {
                throw new DomainException;
            }

            return;
        }

        $this->items[strval($varietyId)] = $varietyRepository
            ->find($varietyId)
            ->createItemWithQuantity($increment);

        if ($this->getWeight() > $this->getCapacity()) {
            throw new DomainException;
        }
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

    public function filterByResource(UuidInterface $filterResourceId): iterable
    {
        $filteredItems = [];

        foreach ($this->items as $item) {
            foreach ($item->getVariety()->getResources() as $itemResource) {
                if ($itemResource->getId()->equals($filterResourceId)) {
                    $filteredItems[] = $item;
                }
            }
        }

        return $filteredItems;
    }
}
