<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\App\UnitOfWork;
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
        ?Inventory $inventory
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->varietyId = $varietyId;
        $this->label = $label;
        $this->icon = $icon;
        $this->isIntact = $isIntact;
        $this->construction = $construction;
        $this->inventory = $inventory;
        $this->resourceNeeds = [];

        foreach ($resourceNeeds as $resourceNeed) {
            if (!$resourceNeed instanceof ResourceNeed) {
                throw new DomainException;
            }

            $this->resourceNeeds[strval($resourceNeed->getResource()->getId())] = $resourceNeed;
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

    public function findResourceNeed(UuidInterface $resourceId): ?ResourceNeed
    {
        if (!array_key_exists(strval($resourceId), $this->resourceNeeds)) {
            return null;
        }

        return $this->resourceNeeds[strval($resourceId)];
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

    public function relabel(string $label): void
    {
        $this->label = $label;
    }

    public function proceedToNextTurn(UnitOfWork $unitOfWork): void
    {
        if ($this->varietyId->equals(Uuid::fromString(VarietyRepositoryConfig::GARDEN_PLOT))) {
            foreach ($this->inventory->getEntities() as $inventoryEntity) {
                $inventoryEntity->construction = $inventoryEntity->construction->takeAStep();
                $unitOfWork->save($inventoryEntity);
            }
        }
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

    public function hasItemWithResourceContent(UuidInterface $resourceId): bool
    {
        $inventoryItemsWithResource = [];

        foreach ($this->inventory->filterByResource($resourceId) as $item) {
            $inventoryItemsWithResource[] = $item;
        }

        return count($inventoryItemsWithResource) > 0;
    }

    public function consumeItemForResourceNeed(UuidInterface $resourceId): void
    {
        $resourceNeed = $this->findResourceNeed($resourceId);

        if (is_null($resourceNeed)) {
            throw new DomainException;
        }

        $lastConsumedVarietyId = $resourceNeed->getLastConsumedVarietyId();

        if (!is_null($lastConsumedVarietyId)
            && $this->inventory->containsItem($lastConsumedVarietyId)
        ) {
            $this->consumeItem($lastConsumedVarietyId);
            return;
        }

        $inventoryItemsWithResource = [];

        foreach ($this->inventory->filterByResource($resourceId) as $item) {
            $inventoryItemsWithResource[] = $item;
        }

        if (count($inventoryItemsWithResource) > 0) {
            $this->consumeItem($inventoryItemsWithResource[0]->getVariety()->getId());
            return;
        }

        throw new DomainException;
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

        $this->inventory = new Inventory($this->id, 10000, [
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity(8),
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                ->createItemWithQuantity(3),
        ], []);
    }
}
