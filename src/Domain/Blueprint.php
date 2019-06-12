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

    public function canBeginConstruction(Inventory $actorInventory, iterable $otherInventories): bool
    {
        return $this->inventoryContainsTools($actorInventory)
            && $this->inventoriesContainsMaterials($actorInventory, $otherInventories);
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

    private function inventoriesContainsMaterials(Inventory $actorInventory, iterable $otherInventories): bool
    {
        $counters = [];

        foreach ($this->materials as $material => $quantity) {
            $counters[$material] = 0;
        }

        $inventories = $this->mergeInventories($actorInventory, $otherInventories);

        foreach ($this->materials as $material => $quantity) {
            foreach ($inventories as $inventory) {
                if ($inventory->containsItem(Uuid::fromString($material))) {
                    $counters[$material] += $inventory->getItem(Uuid::fromString($material))->getQuantity();
                }
            }
        }

        foreach ($this->materials as $material => $quantity) {
            if ($counters[$material] < $quantity) {
                return false;
            }
        }

        return true;
    }

    public function discardUsedMaterials(Inventory $actorInventory, iterable $otherInventories): void
    {
        $counters = [];

        foreach ($this->materials as $material => $quantity) {
            $counters[$material] = $quantity;
        }

        $inventories = $this->mergeInventories($actorInventory, $otherInventories);

        foreach ($this->materials as $material => $quantity) {
            foreach ($inventories as $inventory) {
                $materialVarietyId = Uuid::fromString($material);

                if ($counters[$material] > 0
                    && $inventory->containsItem($materialVarietyId)
                ) {
                    $quantityAvailable = $inventory->getItem($materialVarietyId)->getQuantity();

                    if ($quantityAvailable >= $counters[$material]) {
                        $inventory->discardItem($materialVarietyId, $counters[$material]);
                        $counters[$material] = 0;
                    } else {
                        $inventory->discardItem($materialVarietyId, $quantityAvailable);
                        $counters[$material] -= $quantityAvailable;
                    }
                }
            }
        }
    }

    private function mergeInventories(Inventory $actorInventory, iterable $otherInventories): iterable
    {
        $inventories = [$actorInventory];

        foreach ($otherInventories as $inventory) {
            $inventories[] = $inventory;
        }

        return $inventories;
    }
}
