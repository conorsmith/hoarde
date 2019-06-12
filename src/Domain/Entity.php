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

    /** @var array */
    private $inventoryItems;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        UuidInterface $varietyId,
        string $label,
        string $icon,
        bool $isIntact,
        Construction $construction,
        iterable $resourceNeeds,
        iterable $inventoryItems
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->varietyId = $varietyId;
        $this->label = $label;
        $this->icon = $icon;
        $this->isIntact = $isIntact;
        $this->construction = $construction;
        $this->resourceNeeds = [];
        $this->inventoryItems = [];

        foreach ($resourceNeeds as $resourceNeed) {
            if (!$resourceNeed instanceof ResourceNeed) {
                throw new DomainException;
            }

            $this->resourceNeeds[strval($resourceNeed->getResource()->getId())] = $resourceNeed;
        }

        foreach ($inventoryItems as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->inventoryItems[strval($item->getVariety()->getId())] = $item;
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

    public function getInventory(): iterable
    {
        return $this->inventoryItems;
    }

    public function hasItemInInventory(UuidInterface $varietyId): bool
    {
        return array_key_exists(strval($varietyId), $this->inventoryItems);
    }

    public function hasItemsAmountingToAtLeast(UuidInterface $varietyId, int $minimumQuantity): bool
    {
        if (!array_key_exists(strval($varietyId), $this->inventoryItems)) {
            return false;
        }

        return $this->inventoryItems[strval($varietyId)]->getQuantity() >= $minimumQuantity;
    }

    public function getInventoryWeight(): int
    {
        $weight = 0;

        foreach ($this->inventoryItems as $item) {
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

    public function hasInventory(): bool
    {
        return !$this->varietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WELL));
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
        $item = $this->inventoryItems[strval($id)];

        foreach ($item->getVariety()->getResources() as $resource) {
            if ($resource->getId()->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))) {
                $this->usePringles();

            } elseif (array_key_exists(strval($resource->getId()), $this->resourceNeeds)) {
                $this->useResourceReplenishingItem($resource->getId(), $item->getVariety());
            }
        }

        $this->removeQuantityFromItem(1, $item);

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
        if (!array_key_exists(strval($id), $this->inventoryItems)) {
            throw new DomainException;
        }

        $item = $this->inventoryItems[strval($id)];
        $this->removeQuantityFromItem($quantity, $item);

        return $item;
    }

    public function addItem(Item $item): void
    {
        if (array_key_exists(strval($item->getVariety()->getId()), $this->inventoryItems)) {
            $this->inventoryItems[strval($item->getVariety()->getId())]->add($item->getQuantity());
        } else {
            $this->inventoryItems[strval($item->getVariety()->getId())] = $item;
        }
    }

    public function hasToolsFor(Entity $target): bool
    {
        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            return array_key_exists(VarietyRepositoryConfig::SHOVEL, $this->inventoryItems);
        }

        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            return array_key_exists(VarietyRepositoryConfig::HAMMER, $this->inventoryItems)
                && array_key_exists(VarietyRepositoryConfig::HAND_SAW, $this->inventoryItems);
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

    private function hasStorage(GameRepository $gameRepository, EntityRepository $entityRepository): bool
    {
        if (array_key_exists(VarietyRepositoryConfig::WOODEN_CRATE, $this->inventoryItems)) {
            return true;
        }

        $entityIds = $gameRepository->findEntityIds($this->gameId);

        foreach ($entityIds as $entityId) {
            $entity = $entityRepository->find($entityId);
            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
                return true;
            }
        }

        return false;
    }

    public function addHaulToInventory(ScavengingHaul $haul): void
    {
        foreach ($haul->getItems() as $item) {
            $this->addToInventory($item);
        }
    }

    public function reduceInventoryItemQuantity(UuidInterface $varietyId, int $newQuantity): void
    {
        if ($newQuantity === 0) {
            unset($this->inventoryItems[strval($varietyId)]);
        } else {
            $this->inventoryItems[strval($varietyId)]->reduceTo($newQuantity);
        }
    }

    public function incrementInventoryItemQuantity(
        UuidInterface $varietyId,
        int $increment,
        VarietyRepository $varietyRepository
    ): void {
        if (!array_key_exists(strval($varietyId), $this->inventoryItems)) {
            $this->inventoryItems[strval($varietyId)] = $varietyRepository
                ->find($varietyId)
                ->createItemWithQuantity($increment);
        } else {
            $this->inventoryItems[strval($varietyId)]->incrementBy($increment);
        }

        if ($this->getInventoryWeight() > $this->getInventoryCapacity()) {
            throw new DomainException("{$this->label} cannot carry that much!");
        }
    }

    public function decrementInventoryItemQuantity(UuidInterface $varietyId, int $decrement): void
    {
        if ($this->inventoryItems[strval($varietyId)]->getQuantity() - $decrement === 0) {
            unset($this->inventoryItems[strval($varietyId)]);
        } else {
            $this->inventoryItems[strval($varietyId)]->decrementBy($decrement);
        }
    }

    public function wait(): void
    {
        $this->beforeAction();
        $this->afterAction();
    }

    private function removeQuantityFromItem(int $quantity, Item $item): void
    {
        if ($item->moreThan($quantity)) {
            $item->remove($quantity);
        } else {
            unset($this->inventoryItems[strval($item->getVariety()->getId())]);
        }
    }

    private function addToInventory(Item $addedItem): void
    {
        $key = strval($addedItem->getVariety()->getId());

        if (array_key_exists($key, $this->inventoryItems)) {
            $this->inventoryItems[$key]->add($addedItem->getQuantity());
        } else {
            $this->inventoryItems[$key] = $addedItem;
        }
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

        $this->inventoryItems = [
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity(8),
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                ->createItemWithQuantity(3),
        ];
    }
}
