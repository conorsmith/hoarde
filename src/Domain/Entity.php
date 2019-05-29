<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

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

            $this->resourceNeeds[strval($resourceNeed->getResourceId())] = $resourceNeed;
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

    public function useItem(UuidInterface $id): Item
    {
        foreach ($this->inventory as $key => $item) {
            if ($item->getVariety()->getId()->equals($id)) {
                $this->replenish($item->getVariety()->getResourceId());
                if ($item->moreThanOne()) {
                    $item->removeOne();
                } else {
                    unset($this->inventory[$key]);
                }
                return $item;
            }
        }
    }

    public function dropItem(UuidInterface $id, int $quantity): Item
    {
        $item = $this->inventory[strval($id)];

        if ($item->moreThan($quantity)) {
            $item->remove($quantity);
        } else {
            unset($this->inventory[strval($id)]);
        }

        $this->consumeResources();

        return $item;
    }

    public function scavenge(VarietyRepository $varietyRepository): ?Item
    {
        $generator = (new Factory)->getMediumStrengthGenerator();

        $d100 = $generator->generateInt(1, 100);

        if ($d100 === 100) {
            $scavengedItem = $varietyRepository
                ->find(Uuid::fromString("08db1181-2bc9-4408-b378-5270e8dbee4b"))
                ->createItemWithQuantity(1);
            $this->addToInventory($scavengedItem);
        } elseif ($d100 === 99) {
            $scavengedItem = $varietyRepository
                ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
                ->createItemWithQuantity(1);
            $this->addToInventory($scavengedItem);
        } else {

            $diceRoll = $generator->generateInt(1, 6);

            if ($diceRoll === 4 && $this->isAddictedToHeroin()) {
                $scavengedItem = $varietyRepository
                    ->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"))
                    ->createItemWithQuantity(1);
                $this->addToInventory($scavengedItem);

            } elseif ($diceRoll === 5) {
                $scavengedItem = $varietyRepository
                    ->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d"))
                    ->createItemWithQuantity($generator->generateInt(1, 3));
                $this->addToInventory($scavengedItem);

            } elseif ($diceRoll === 6) {
                $scavengedItem = $varietyRepository
                    ->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158"))
                    ->createItemWithQuantity($generator->generateInt(1, 2));
                $this->addToInventory($scavengedItem);

            } else {
                $scavengedItem = null;
            }
        }

        $this->consumeResources();

        return $scavengedItem;
    }

    private function addToInventory(Item $addedItem): void
    {
        if (array_key_exists(strval($addedItem->getVariety()->getId()), $this->inventory)) {
            $this->inventory[strval($addedItem->getVariety()->getId())]->add($addedItem->getQuantity());
        } else {
            $this->inventory[strval($addedItem->getVariety()->getId())] = $addedItem;
        }
    }

    public function wait(): void
    {
        $this->consumeResources();
    }

    private function isAddictedToHeroin(): bool
    {
        return array_key_exists("5234c112-05be-4b15-80df-3c2b67e88262", $this->resourceNeeds);
    }

    public function replenish(UuidInterface $resourceId): void
    {
        if ($resourceId->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))
            && !$this->isAddictedToHeroin()
        ) {
            $this->consumeResources();

            $this->resourceNeeds["5234c112-05be-4b15-80df-3c2b67e88262"] = new ResourceNeed(
                Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"),
                12,
                12
            );
        } else {
            foreach ($this->resourceNeeds as $key => $resourceNeed) {
                if ($resourceNeed->getResourceId()->equals($resourceId)) {
                    $this->resourceNeeds[$key] = $resourceNeed->replenish();
                } else {
                    $this->resourceNeeds[$key] = $this->consumeResource($resourceNeed);
                }
            }
        }
    }

    private function consumeResources(): void
    {
        foreach ($this->resourceNeeds as $key => $resourceNeed) {
            $this->resourceNeeds[$key] = $this->consumeResource($resourceNeed);
        }
    }

    private function consumeResource(ResourceNeed $resourceNeed): ResourceNeed
    {
        $resourceNeed = $resourceNeed->consume();

        if ($resourceNeed->isDepleted()) {
            $this->isIntact = false;
        }

        return $resourceNeed;
    }

    public function reset(VarietyRepository $varietyRepository): void
    {
        $this->isIntact = true;

        $this->resourceNeeds = [
            "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => new ResourceNeed(
                Uuid::fromString("6f5cc44d-db25-454a-b3fb-4ab3f61ce179"),
                3,
                0 // Hack
            ),
            "9972c015-842a-4601-8fb2-c900e1a54177" => new ResourceNeed(
                Uuid::fromString("9972c015-842a-4601-8fb2-c900e1a54177"),
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
