<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class ResourceNeed
{
    /** @var Resource */
    private $resource;

    /** @var int */
    private $currentLevel;

    /** @var int */
    private $maximumLevel;

    /** @var int */
    private $amountPerLevel;

    /** @var ?UuidInterface */
    private $lastConsumedVarietyId;

    public function __construct(
        Resource $resource,
        int $currentLevel,
        int $maximumLevel,
        int $amountPerLevel,
        ?UuidInterface $lastConsumedVarietyId
    ) {
        $this->resource = $resource;
        $this->currentLevel = $currentLevel;
        $this->maximumLevel = $maximumLevel;
        $this->amountPerLevel = $amountPerLevel;
        $this->lastConsumedVarietyId = $lastConsumedVarietyId;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resource->getId();
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function getCurrentLevel(): int
    {
        return $this->currentLevel;
    }

    public function getMaximumLevel(): int
    {
        return $this->maximumLevel;
    }

    public function getLastConsumedVarietyId(): ?UuidInterface
    {
        return $this->lastConsumedVarietyId;
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
            $this->resource,
            $newLevel,
            $this->maximumLevel,
            $this->amountPerLevel,
            $this->lastConsumedVarietyId
        );
    }

    public function replenish(Variety $variety): self
    {
        $resourceContent = $variety->findResourceContent($this->resource);

        $levelsReplenished = intval(floor($resourceContent->getAmount() / $this->amountPerLevel));

        return new self(
            $this->resource,
            min(
                $this->currentLevel + $levelsReplenished,
                $this->maximumLevel
            ),
            $this->maximumLevel,
            $this->amountPerLevel,
            $variety->getId()
        );
    }
}
