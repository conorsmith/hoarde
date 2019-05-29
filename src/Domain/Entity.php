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

    /** @var bool */
    private $isIntact;

    /** @var array */
    private $resourceNeeds;

    /** @var array */
    private $inventory;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        bool $isIntact,
        iterable $resourceNeeds,
        iterable $inventory
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
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

        if ($resourceId->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))
            && !$this->isAddictedToHeroin()
        ) {
            $resourceRepository = new ResourceRepositoryConfig;

            $this->resourceNeeds["5234c112-05be-4b15-80df-3c2b67e88262"] = new ResourceNeed(
                $resourceRepository->find(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262")),
                12,
                12
            );
        }

        $this->resourceNeeds[strval($resourceId)] = $this->resourceNeeds[strval($resourceId)]->replenish();

        $this->removeQuantityFromItem(1, $item);

        $this->afterAction();

        return $item;
    }

    public function dropItem(UuidInterface $id, int $quantity): Item
    {
        $this->beforeAction();

        $item = $this->inventory[strval($id)];
        $this->removeQuantityFromItem($quantity, $item);

        $this->afterAction();

        return $item;
    }

    public function scavenge(VarietyRepository $varietyRepository): ?Item
    {
        $this->beforeAction();

        $generator = (new Factory)->getMediumStrengthGenerator();

        $rollTable = [
            [
                'rolls' => [100],
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("08db1181-2bc9-4408-b378-5270e8dbee4b"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => [99],
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
                    ->createItemWithQuantity(1),
            ],
            [
                'rolls' => range(18, 34),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d"))
                    ->createItemWithQuantity($generator->generateInt(1, 3)),
            ],
            [
                'rolls' => range(1, 17),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158"))
                    ->createItemWithQuantity($generator->generateInt(1, 2)),
            ],
        ];

        if ($this->isAddictedToHeroin()) {
            $rollTable[] = [
                'rolls' => range(35, 51),
                'item'  => $varietyRepository
                    ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
                    ->createItemWithQuantity(1),
            ];
        }

        $scavengedItem = null;

        $d100 = $generator->generateInt(1, 100);

        foreach ($rollTable as $rollTableEntry) {
            if (in_array($d100, $rollTableEntry['rolls'])) {
                $scavengedItem = $rollTableEntry['item'];
            }
        }

        if (!is_null($scavengedItem)) {
            $this->addToInventory($scavengedItem);
        }

        $this->afterAction();

        return $scavengedItem;
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

    private function isAddictedToHeroin(): bool
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
