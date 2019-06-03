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

    /** @var array */
    private $resourceNeeds;

    /** @var array */
    private $inventory;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        UuidInterface $varietyId,
        string $label,
        string $icon,
        bool $isIntact,
        iterable $resourceNeeds,
        iterable $inventory
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->varietyId = $varietyId;
        $this->label = $label;
        $this->icon = $icon;
        $this->isIntact = $isIntact;
        $this->resourceNeeds = [];
        $this->inventory = [];

        foreach ($resourceNeeds as $resourceNeed) {
            if (!$resourceNeed instanceof ResourceNeed) {
                throw new DomainException;
            }

            $this->resourceNeeds[strval($resourceNeed->getResource()->getId())] = $resourceNeed;
        }

        foreach ($inventory as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->inventory[strval($item->getVariety()->getId())] = $item;
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

    public function getResourceNeeds(): iterable
    {
        return $this->resourceNeeds;
    }

    public function getInventory(): iterable
    {
        return $this->inventory;
    }

    public function getInventoryWeight(): int
    {
        $weight = 0;

        foreach ($this->inventory as $item) {
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

    public function useItem(UuidInterface $id): Item
    {
        $this->beforeAction();

        $item = $this->inventory[strval($id)];

        foreach ($item->getVariety()->getResources() as $resource) {
            if ($resource->getId()->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))) {
                $this->usePringles();

            } elseif (array_key_exists(strval($resource->getId()), $this->resourceNeeds)) {
                $this->useResourceReplenishingItem($resource->getId(), $item->getVariety());
            }
        }

        $this->removeQuantityFromItem(1, $item);

        $this->afterAction();

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

            $this->resourceNeeds["5234c112-05be-4b15-80df-3c2b67e88262"] = new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262")),
                12,
                12,
                Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262")
            );
        }
    }

    public function dropItem(UuidInterface $id, int $quantity): Item
    {
        $item = $this->inventory[strval($id)];
        $this->removeQuantityFromItem($quantity, $item);

        return $item;
    }

    public function addItem(Item $item): void
    {
        if (array_key_exists(strval($item->getVariety()->getId()), $this->inventory)) {
            $this->inventory[strval($item->getVariety()->getId())]->add($item->getQuantity());
        } else {
            $this->inventory[strval($item->getVariety()->getId())] = $item;
        }
    }

    public function scavenge(
        VarietyRepository $varietyRepository,
        GameRepository $gameRepository,
        EntityRepository $entityRepository
    ): ScavengingHaul {
        $this->beforeAction();

        $generator = (new Factory)->getLowStrengthGenerator();

        $rollTable = [
            [
                'rolls' => range(980, 989),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::COKE_ZERO))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(970, 979),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PRINGLE))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(965, 969),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::CHERRY_COKE_ZERO))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(960, 964),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::VANILLA_COKE_ZERO))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(955, 959),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PEACH_COKE_ZERO))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(950, 954),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::GINGER_COKE_ZERO))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(940, 944),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_DREW))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(907, 909),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::BUCKET))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(904, 906),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::ROPE))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(901, 903),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::SHOVEL))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => array_merge(range(1, 10), range(290, 330)),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_SOUP))
                    ->createItemWithQuantity($generator->generateInt(1, 2)),
            ],
            [
                'rolls' => range(140, 300),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                    ->createItemWithQuantity($generator->generateInt(1, 3)),
            ],
            [
                'rolls' => range(1, 170),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                    ->createItemWithQuantity($generator->generateInt(1, 2)),
            ],
        ];

        if (!$this->hasStorage($gameRepository, $entityRepository)) {
            $rollTable[] = [
                'rolls' => range(996, 1000),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))
                    ->createItemWithQuantity(1),
            ];
        }

        if ($this->needsPringles()) {
            $rollTable[] = [
                'rolls' => range(350, 510),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PRINGLE))
                    ->createItemWithQuantity(1),
            ];
        }

        $scavengedItems = [];

        $d1000 = $generator->generateInt(1, 1000);

        foreach ($rollTable as $rollTableEntry) {
            if (in_array($d1000, $rollTableEntry['rolls'])) {
                $scavengedItems[] = $rollTableEntry['item'];
            }
        }

        $haul = new ScavengingHaul(Uuid::uuid4(), $scavengedItems);

        $this->afterAction();

        return $haul;
    }

    private function hasStorage(GameRepository $gameRepository, EntityRepository $entityRepository): bool
    {
        if (array_key_exists(VarietyRepositoryConfig::WOODEN_CRATE, $this->inventory)) {
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
            unset($this->inventory[strval($varietyId)]);
        } else {
            $this->inventory[strval($varietyId)]->reduceTo($newQuantity);
        }
    }

    public function incrementInventoryItemQuantity(
        UuidInterface $varietyId,
        int $increment,
        VarietyRepository $varietyRepository
    ): void {
        if (!array_key_exists(strval($varietyId), $this->inventory)) {
            $this->inventory[strval($varietyId)] = $varietyRepository
                ->find($varietyId)
                ->createItemWithQuantity($increment);
        } else {
            $this->inventory[strval($varietyId)]->incrementBy($increment);
        }

        if ($this->getInventoryWeight() > $this->getInventoryCapacity()) {
            throw new DomainException("{$this->label} cannot carry that much!");
        }
    }

    public function decrementInventoryItemQuantity(UuidInterface $varietyId, int $decrement): void
    {
        if ($this->inventory[strval($varietyId)]->getQuantity() - $decrement === 0) {
            unset($this->inventory[strval($varietyId)]);
        } else {
            $this->inventory[strval($varietyId)]->decrementBy($decrement);
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
            unset($this->inventory[strval($item->getVariety()->getId())]);
        }
    }

    private function addToInventory(Item $addedItem): void
    {
        $key = strval($addedItem->getVariety()->getId());

        if (array_key_exists($key, $this->inventory)) {
            $this->inventory[$key]->add($addedItem->getQuantity());
        } else {
            $this->inventory[$key] = $addedItem;
        }
    }

    private function needsPringles(): bool
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
            "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("6f5cc44d-db25-454a-b3fb-4ab3f61ce179")),
                3,
                0, // Hack
                null
            ),
            "9972c015-842a-4601-8fb2-c900e1a54177" => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("9972c015-842a-4601-8fb2-c900e1a54177")),
                3,
                0, // Hack
                null
            )
        ];

        $this->inventory = [
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity(8),
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                ->createItemWithQuantity(3),
        ];
    }
}
