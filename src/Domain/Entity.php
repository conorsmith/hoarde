<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RandomLib\Factory;

final class Entity
{
    /** @var UuidInterface */
    private $id;

    /** @var UuidInterface */
    private $gameId;

    /** @var UuidInterface */
    private $varietyId;

    /** @var string */
    private $label;

    /** @var string */
    private $icon;

    /** @var bool */
    private $isIntact;

    /** @var Construction */
    private $construction;

    /** @var array */
    private $resourceNeeds;

    /** @var ?Inventory */
    private $inventory;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        UuidInterface $varietyId,
        string $label,
        string $icon,
        bool $isIntact,
        Construction $construction,
        iterable $resourceNeeds,
        ?iterable $inventoryItems
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->varietyId = $varietyId;
        $this->label = $label;
        $this->icon = $icon;
        $this->isIntact = $isIntact;
        $this->construction = $construction;
        $this->resourceNeeds = [];

        foreach ($resourceNeeds as $resourceNeed) {
            if (!$resourceNeed instanceof ResourceNeed) {
                throw new DomainException;
            }

            $this->resourceNeeds[strval($resourceNeed->getResource()->getId())] = $resourceNeed;
        }

        if (!is_null($inventoryItems)) {
            $this->inventory = new Inventory($this->id, $inventoryItems);
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getGameId(): UuidInterface
    {
        return $this->gameId;
    }

    public function getVarietyId(): UuidInterface
    {
        return $this->varietyId;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function isIntact(): bool
    {
        return $this->isIntact;
    }

    public function getConstruction(): Construction
    {
        return $this->construction;
    }

    public function getResourceNeeds(): iterable
    {
        return $this->resourceNeeds;
    }

    public function getInventoryItems(): iterable
    {
        return $this->inventory->getItems();
    }

    public function hasInventory(): bool
    {
        return !is_null($this->inventory);
    }

    public function getInventory(): Inventory
    {
        if (!$this->hasInventory()) {
            throw new DomainException;
        }

        return $this->inventory;
    }

    public function hasItemInInventory(UuidInterface $varietyId): bool
    {
        return $this->inventory->containsItem($varietyId);
    }

    public function hasItemsAmountingToAtLeast(UuidInterface $varietyId, int $minimumQuantity): bool
    {
        return $this->inventory->containsItemAmountingToAtLeast($varietyId, $minimumQuantity);
    }

    public function getInventoryWeight(): int
    {
        $weight = 0;

        foreach ($this->inventory->getItems() as $item) {
            $weight += $item->getWeight();
        }

        return $weight;
    }

    public function getInventoryCapacity(): int
    {
        if ($this->varietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            return 50000;
        }

        return 10000;
    }

    public function isOverencumbered(): bool
    {
        return $this->getInventoryWeight() >= $this->getInventoryCapacity();
    }

    public function relabel(string $label): void
    {
        $this->label = $label;
    }

    private function beforeAction(): void
    {
        $this->consumeResources();
    }

    private function afterAction(): void
    {
        foreach ($this->resourceNeeds as $resourceNeed) {
            if ($resourceNeed->isDepleted()) {
                $this->isIntact = false;
            }
        }
    }

    public function consumeItem(UuidInterface $id): Item
    {
        $item = $this->inventory->getItem($id);

        foreach ($item->getVariety()->getResources() as $resource) {
            if ($resource->getId()->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))) {
                $this->usePringles();

            } elseif (array_key_exists(strval($resource->getId()), $this->resourceNeeds)) {
                $this->useResourceReplenishingItem($resource->getId(), $item->getVariety());
            }
        }

        $this->inventory->removeQuantityFromItem($item, 1);

        return $item;
    }

    private function useResourceReplenishingItem(UuidInterface $resourceId, Variety $variety)
    {
        $this->resourceNeeds[strval($resourceId)] = $this->resourceNeeds[strval($resourceId)]->replenish($variety);
    }

    private function usePringles()
    {
        if (!$this->needsPringles()) {
            $resourceRepository = new ResourceRepositoryConfig;

            $this->resourceNeeds[ResourceRepositoryConfig::PRINGLES] = new ResourceNeed(
                $resourceRepository->find(Uuid::fromString(ResourceRepositoryConfig::PRINGLES)),
                12,
                12,
                Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262")
            );
        }
    }

    public function dropItem(UuidInterface $id, int $quantity): Item
    {
        $this->inventory->discardItem($id, $quantity);

        return $this->inventory->getItem($id);
    }

    public function addItem(Item $item): void
    {
        $this->inventory->addItem($item);
    }

    public function hasToolsFor(Entity $target): bool
    {
        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            return $this->inventory->containsItem(Uuid::fromString(VarietyRepositoryConfig::SHOVEL));
        }

        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            return $this->inventory->containsItem(Uuid::fromString(VarietyRepositoryConfig::HAMMER))
                && $this->inventory->containsItem(Uuid::fromString(VarietyRepositoryConfig::HAND_SAW));
        }

        return false;
    }

    public function construct(Entity $target): void
    {
        $this->beforeAction();

        $target->construction = $target->construction->takeAStep();

        $this->afterAction();
    }

    public function scavenge(Scavenge $scavenge): ScavengingHaul
    {
        for ($i = 0; $i < $scavenge->getLength(); $i++) {
            $this->beforeAction();

            if ($i + 1 === $scavenge->getLength()) {
                $haul = $scavenge->roll();
            }

            $this->afterAction();
        }

        if (!$this->isIntact) {
            return ScavengingHaul::empty();
        }

        return $haul;
    }

    public function addHaulToInventory(ScavengingHaul $haul): void
    {
        foreach ($haul->getItems() as $item) {
            $this->inventory->addItem($item);
        }
    }

    public function reduceInventoryItemQuantity(UuidInterface $varietyId, int $newQuantity): void
    {
        $this->inventory->reduceItemQuantityTo($varietyId, $newQuantity);
    }

    public function incrementInventoryItemQuantity(
        UuidInterface $varietyId,
        int $increment,
        VarietyRepository $varietyRepository
    ): void {
        $this->inventory->incrementItemQuantity($varietyId, $increment, $varietyRepository);

        if ($this->getInventoryWeight() > $this->getInventoryCapacity()) {
            throw new DomainException("{$this->label} cannot carry that much!");
        }
    }

    public function decrementInventoryItemQuantity(UuidInterface $varietyId, int $decrement): void
    {
        $this->inventory->decrementItemQuantity($varietyId, $decrement);
    }

    public function wait(): void
    {
        $this->beforeAction();
        $this->afterAction();
    }

    public function needsPringles(): bool
    {
        return array_key_exists("5234c112-05be-4b15-80df-3c2b67e88262", $this->resourceNeeds);
    }

    private function consumeResources(): void
    {
        foreach ($this->resourceNeeds as $key => $resourceNeed) {
            $this->resourceNeeds[$key] = $resourceNeed->consume();
        }
    }

    public function reset(VarietyRepository $varietyRepository, ResourceRepository $resourceRepository): void
    {
        $this->isIntact = true;

        $this->resourceNeeds = [
            ResourceRepositoryConfig::FOOD => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString(ResourceRepositoryConfig::FOOD)),
                3,
                0, // Hack
                60,
                null
            ),
            ResourceRepositoryConfig::WATER => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString(ResourceRepositoryConfig::WATER)),
                3,
                0, // Hack
                100,
                null
            )
        ];

        $this->inventory = new Inventory($this->id, [
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity(8),
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                ->createItemWithQuantity(3),
        ]);
    }
}
