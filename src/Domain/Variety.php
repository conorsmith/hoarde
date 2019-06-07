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
    private $resources;

    /** @var int */
    private $weight;

    /** @var string */
    private $icon;

    /** @var string */
    private $description;

    /** @var iterable */
    private $actions;

    public function __construct(
        UuidInterface $id,
        string $label,
        iterable $resources,
        int $weight,
        string $icon,
        string $description,
        iterable $actions
    ) {
        $this->id = $id;
        $this->label = $label;
        $this->weight = $weight;
        $this->icon = $icon;
        $this->description = $description;

        $this->resources = [];
        $this->actions = [];

        foreach ($resources as $resource) {
            if (!$resource instanceof Resource) {
                throw new DomainException;
            }

            $this->resources[strval($resource->getId())] = $resource;
        }

        foreach ($actions as $action) {
            if (!$action instanceof Action) {
                throw new DomainException;
            }

            $this->actions[strval($action->getId())] = $action;
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
        return $this->resources;
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

    public function getActions(): iterable
    {
        return $this->actions;
    }

    public function createItemWithQuantity(int $quantity): Item
    {
        return new Item($this, $quantity);
    }
}
