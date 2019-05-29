<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class ResourceNeed
{
    /** @var UuidInterface */
    private $resourceId;

    /** @var int */
    private $currentLevel;

    /** @var int */
    private $maximumLevel;

    public function __construct(UuidInterface $resourceId, int $currentLevel, int $maximumLevel)
    {
        $this->resourceId = $resourceId;
        $this->currentLevel = $currentLevel;
        $this->maximumLevel = $maximumLevel;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resourceId;
    }

    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    public function getMaximumLevel(): int
    {
        return $this->maximumLevel;
    }

    public function isDepleted(): bool
    {
        return $this->currentLevel === 0;
    }

    public function consume(): self
    {
        $newLevel = $this->currentLevel - 1;

        if ($newLevel < 0) {
            $newLevel = 0;
        }

        return new self(
            $this->resourceId,
            $newLevel,
            $this->maximumLevel
        );
    }

    public function replenish(): self
    {
        return new self(
            $this->resourceId,
            $this->maximumLevel,
            $this->maximumLevel
        );
    }
}
