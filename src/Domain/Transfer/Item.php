<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain\Transfer;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Item
{
    /** @var UuidInterface */
    private $varietyId;

    /** @var int */
    private $quantity;

    public function __construct(UuidInterface $varietyId, int $quantity)
    {
        $this->varietyId = $varietyId;
        $this->quantity = $quantity;

        if ($this->quantity < 0) {
            throw new DomainException;
        }
    }

    public function getVarietyId(): UuidInterface
    {
        return $this->varietyId;
    }

    public function getQuantity(): int
    {
        return $this->quantity;
    }

    public function isEmpty(): bool
    {
        return $this->quantity === 0;
    }
}
