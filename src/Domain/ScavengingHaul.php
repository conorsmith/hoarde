<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class ScavengingHaul
{
    /** @var UuidInterface */
    private $id;

    /** @var array */
    private $items;

    public function __construct(UuidInterface $id, iterable $items)
    {
        $this->id = $id;
        $this->items = [];

        foreach ($items as $item) {
            $this->items[strval($item->getVariety()->getId())] = $item;
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getItems(): iterable
    {
        return $this->items;
    }

    public function hasItems(): bool
    {
        foreach ($this->items as $item) {
            return true;
        }

        return false;
    }

    public function getWeight(): int
    {
        $weight = 0;

        foreach ($this->items as $item) {
            $weight += $item->getVariety()->getWeight() * $item->getQuantity();
        }

        return $weight;
    }

    public function isRetrievableBy(Entity $entity): bool
    {
        return $entity->getInventoryWeight() + $this->getWeight() <= $entity->getInventoryCapacity();
    }

    public function reduceItemQuantity(UuidInterface $varietyId, int $quantity): void
    {
        if ($quantity === 0) {
            unset($this->items[strval($varietyId)]);
            return;
        }

        $item = $this->items[strval($varietyId)];

        if ($item->getQuantity() > $quantity) {
            $item->reduceTo($quantity);
            return;
        }
    }
}
