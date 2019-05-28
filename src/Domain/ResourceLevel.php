<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class ResourceLevel
{
    /** @var UuidInterface */
    private $resourceId;

    /** @var int */
    private $value;

    public function __construct(UuidInterface $resourceId, int $value)
    {
        $this->resourceId = $resourceId;
        $this->value = $value;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resourceId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function consume(): self
    {
        $newLevel = $this->value - 1;

        if ($newLevel < 0) {
            $newLevel = 0;
        }

        return new self(
            $this->resourceId,
            $newLevel
        );
    }

    public function replenish(): self
    {
        return new self(
            $this->resourceId,
            5
        );
    }
}
