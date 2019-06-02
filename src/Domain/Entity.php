<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;
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
        if ($this->varietyId->equals(Uuid::fromString("59593b72-3845-491e-9721-4452a337019b"))) {
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

        $resourceId = $item->getVariety()->getResource()->getId();

        if ($resourceId->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))) {
            $this->usePringles();

        } elseif (array_key_exists(strval($resourceId), $this->resourceNeeds)) {
            $this->useResourceReplenishingItem($resourceId);
        }

        $this->removeQuantityFromItem(1, $item);

        $this->afterAction();

        return $item;
    }

    private function useResourceReplenishingItem(UuidInterface $resourceId)
    {
        $this->resourceNeeds[strval($resourceId)] = $this->resourceNeeds[strval($resourceId)]->replenish();
    }

    private function usePringles()
    {
        if (!$this->needsPringles()) {
            $resourceRepository = new ResourceRepositoryConfig;

            $this->resourceNeeds["5234c112-05be-4b15-80df-3c2b67e88262"] = new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262")),
                12,
                12
            );
        }
    }

    public function dropItem(UuidInterface $id, int $quantity): Item
    {
        $item = $this->inventory[strval($id)];
        $this->removeQuantityFromItem($quantity, $item);

        return $item;
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
                    ->find(Uuid::fromString("08db1181-2bc9-4408-b378-5270e8dbee4b"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(970, 979),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(965, 969),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("450349d4-fe21-4da0-8f78-99c684b05b45"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(960, 964),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("813980ad-7604-4713-909c-b2701420de1b"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(955, 959),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("e12981d2-5873-454a-b297-895f42e66bd5"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(950, 954),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("5de1c51c-2747-426d-a3b0-c854107c7132"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(940, 944),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("cf057538-d3f0-4657-8a4c-f911bc113ad7"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(140, 300),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d"))
                    ->createItemWithQuantity($generator->generateInt(1, 3)),
            ],
            [
                'rolls' => range(1, 170),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158"))
                    ->createItemWithQuantity($generator->generateInt(1, 2)),
            ],
        ];

        if (!$this->hasStorage($gameRepository, $entityRepository)) {
            $rollTable[] = [
                'rolls' => range(996, 1000),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("59593b72-3845-491e-9721-4452a337019b"))
                    ->createItemWithQuantity(1),
            ];
        }

        if ($this->needsPringles()) {
            $rollTable[] = [
                'rolls' => range(350, 510),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
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
        if (array_key_exists("59593b72-3845-491e-9721-4452a337019b", $this->inventory)) {
            return true;
        }

        $entityIds = $gameRepository->findEntityIds($this->gameId);

        foreach ($entityIds as $entityId) {
            $entity = $entityRepository->find($entityId);
            if ($entity->getVarietyId()->equals(Uuid::fromString("59593b72-3845-491e-9721-4452a337019b"))) {
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
                0 // Hack
            ),
            "9972c015-842a-4601-8fb2-c900e1a54177" => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("9972c015-842a-4601-8fb2-c900e1a54177")),
                3,
                0 // Hack
            )
        ];

        $this->inventory = [
            $varietyRepository
                ->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d"))
                ->createItemWithQuantity(8),
            $varietyRepository
                ->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158"))
                ->createItemWithQuantity(3),
        ];
    }
}
