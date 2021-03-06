<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;

final class Item
{
    /** @var int */
    private $quantity;

    /** @var Variety */
    private $variety;

    public function __construct(Variety $variety, int $quantity)
    {
        $this->variety = $variety;
        $this->quantity = $quantity;

        if ($this->quantity < 1) {
            throw new DomainException;
        }
    }

    public function getVariety(): Variety
    {
        return $this->variety;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getWeight(): int
    {
        return $this->quantity * $this->variety->getWeight();
    }

    public function moreThanOne(): bool
    {
        return $this->quantity > 1;
    }

    public function moreThan(int $quantity): bool
    {
        return $this->quantity > $quantity;
    }

    public function lessThan(int $quantity): bool
    {
        return $this->quantity < $quantity;
    }

    public function removeOne(): void
    {
        if ($this->quantity < 2) {
            throw new DomainException;
        }

        $this->quantity--;
    }

    public function add(int $additionalQuantity): void
    {
        $this->quantity += $additionalQuantity;
    }

    public function remove(int $removedQuantity): void
    {
        $this->quantity -= $removedQuantity;
    }

    public function reduceTo(int $newQuantity): void
    {
        if ($newQuantity > $this->quantity) {
            throw new DomainException;
        }

        $this->quantity = $newQuantity;
    }

    public function incrementBy(int $increment): void
    {
        $this->quantity += $increment;
    }

    public function decrementBy(int $decrement): void
    {
        $this->quantity -= $decrement;
    }
}
