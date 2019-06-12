<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Blueprint
{
    /** @var array */
    private $tools;

    /** @var array */
    private $materials;

    /** @var int */
    private $turns;

    public function __construct(iterable $tools, iterable $materials, int $turns)
    {
        $this->tools = [];
        $this->materials = [];
        $this->turns = $turns;

        foreach ($tools as $tool) {
            $this->tools[] = $tool;
        }

        foreach ($materials as $material => $quantity) {
            $this->materials[$material] = $quantity;
        }
    }

    public function getTools(): iterable
    {
        return $this->tools;
    }

    public function getMaterials(): iterable
    {
        return $this->materials;
    }

    public function getTurns(): int
    {
        return $this->turns;
    }

    public function canContinueConstruction(Inventory $inventory): bool
    {
        return $this->inventoryContainsTools($inventory);
    }

    public function canBeginConstruction(Inventory $inventory): bool
    {
        return $this->inventoryContainsTools($inventory)
            && $this->inventoryContainsMaterials($inventory);
    }

    private function inventoryContainsTools(Inventory $inventory): bool
    {
        foreach ($this->tools as $tool) {
            if (!$inventory->containsItem(Uuid::fromString($tool))) {
                return false;
            }
        }

        return true;
    }

    private function inventoryContainsMaterials(Inventory $inventory): bool
    {
        foreach ($this->materials as $material => $quantity) {
            if (!$inventory->containsItemAmountingToAtLeast(Uuid::fromString($material), $quantity)) {
                return false;
            }
        }

        return true;
    }
}
