<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class Variety
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    /** @var Resource */
    private $resource;

    /** @var int */
    private $weight;

    public function __construct(UuidInterface $id, string $label, Resource $resource, int $weight)
    {
        $this->id = $id;
        $this->label = $label;
        $this->resource = $resource;
        $this->weight = $weight;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function getWeight(): int
    {
        return $this->weight;
    }

    public function createItemWithQuantity(int $quantity): Item
    {
        return new Item($this, $quantity);
    }
}
