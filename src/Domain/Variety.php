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

    /** @var UuidInterface */
    private $resourceId;

    public function __construct(UuidInterface $id, string $label, UuidInterface $resourceId)
    {
        $this->id = $id;
        $this->label = $label;
        $this->resourceId = $resourceId;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getResourceId(): UuidInterface
    {
        return $this->resourceId;
    }

    public function createItemWithQuantity(int $quantity): Item
    {
        return new Item($this, $quantity);
    }
}
