<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

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

    public function moreThanOne(): bool
    {
        return $this->quantity > 1;
    }

    public function moreThan(int $quantity): bool
    {
        return $this->quantity > $quantity;
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
}
