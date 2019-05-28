<?php
declare(strict_type=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Item
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    /** @var int */
    private $quantity;

    /** @var UuidInterface */
    private $resourceId;

    public function __construct(UuidInterface $id, string $label, int $quantity, UuidInterface $resourceId)
    {
        $this->id = $id;
        $this->label = $label;
        $this->quantity = $quantity;
        $this->resourceId = $resourceId;

        if ($this->quantity < 1) {
            throw new DomainException;
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resourceId;
    }

    public function moreThanOne(): bool
    {
        return $this->quantity > 1;
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
}
