<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Variety
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    /** @var array */
    private $resourceContents;

    /** @var int */
    private $weight;

    /** @var string */
    private $icon;

    /** @var string */
    private $description;

    /** @var bool */
    private $hasInventory;

    /** @var ?int */
    private $inventoryCapacity;

    /** @var iterable */
    private $actions;

    /** @var ?Blueprint */
    private $blueprint;

    /** @var iterable */
    private $resourceNeedCapacities;

    public function __construct(
        UuidInterface $id,
        string $label,
        iterable $resourceContents,
        int $weight,
        string $icon,
        string $description,
        bool $hasInventory,
        ?int $inventoryCapacity,
        iterable $actions,
        ?Blueprint $blueprint,
        iterable $resourceNeedCapacities
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->weight = $weight;
        $this->icon = $icon;
        $this->description = $description;
        $this->hasInventory = $hasInventory;
        $this->inventoryCapacity = $inventoryCapacity;
        $this->blueprint = $blueprint;

        $this->resourceContents = [];
        $this->actions = [];
        $this->resourceNeedCapacities = [];

        foreach ($resourceContents as $resourceContent) {
            if (!$resourceContent instanceof ResourceContent) {
                throw new DomainException;
            }

            $this->resourceContents[strval($resourceContent->getResource()->getId())] = $resourceContent;
        }

        foreach ($actions as $action) {
            if (!$action instanceof Action) {
                throw new DomainException;
            }

            $this->actions[strval($action->getId())] = $action;
        }

        foreach ($resourceNeedCapacities as $resourceId => $capacity) {
            if (!is_int($capacity)) {
                throw new DomainException;
            }

            $this->resourceNeedCapacities[$resourceId] = $capacity;
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

    public function getResources(): iterable
    {
        return array_map(
            function (ResourceContent $resourceContent) {
                return $resourceContent->getResource();
            },
            $this->resourceContents
        );
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function hasInventory(): bool
    {
        return $this->hasInventory;
    }

    public function getInventoryCapacity(): ?int
    {
        return $this->inventoryCapacity;
    }

    public function getActions(): iterable
    {
        return $this->actions;
    }

    public function hasBlueprint(): bool
    {
        return !is_null($this->blueprint);
    }

    public function getBlueprint(): ?Blueprint
    {
        return $this->blueprint;
    }

    public function getResourceNeedCapacities(): iterable
    {
        return $this->resourceNeedCapacities;
    }

    public function createItemWithQuantity(int $quantity): Item
    {
        return new Item($this, $quantity);
    }

    public function findResourceContent(Resource $resource): ?ResourceContent
    {
        if (!array_key_exists(strval($resource->getId()), $this->resourceContents)) {
            return null;
        }

        return $this->resourceContents[strval($resource->getId())];
    }
}
