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

    /** @var int */
    private $maximumValue;

    public function __construct(UuidInterface $resourceId, int $value, int $maximumValue)
    {
        $this->resourceId = $resourceId;
        $this->value = $value;
        $this->maximumValue = $maximumValue;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resourceId;
    }

    public function getValue(): int
    {
        return $this->value;
    }

    public function getMaximumValue(): int
    {
        return $this->maximumValue;
    }

    public function isDepleted(): bool
    {
        return $this->value === 0;
    }

    public function consume(): self
    {
        $newLevel = $this->value - 1;

        if ($newLevel < 0) {
            $newLevel = 0;
        }

        return new self(
            $this->resourceId,
            $newLevel,
            $this->maximumValue
        );
    }

    public function replenish(): self
    {
        return new self(
            $this->resourceId,
            $this->maximumValue,
            $this->maximumValue
        );
    }
}
