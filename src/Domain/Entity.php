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
    private $resourceLevels;

    /** @var array */
    private $inventory;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        bool $isIntact,
        iterable $resourceLevels,
        iterable $inventory
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->isIntact = $isIntact;
        $this->resourceLevels = [];
        $this->inventory = [];

        foreach ($resourceLevels as $resourceLevel) {
            if (!$resourceLevel instanceof ResourceLevel) {
                throw new DomainException;
            }

            $this->resourceLevels[strval($resourceLevel->getResourceId())] = $resourceLevel;
        }

        foreach ($inventory as $item) {
            if (!$item instanceof Item) {
                throw new DomainException;
            }

            $this->inventory[strval($item->getId())] = $item;
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

    public function getResourceLevels(): iterable
    {
        return $this->resourceLevels;
    }

    public function getInventory(): iterable
    {
        return $this->inventory;
    }

    public function useItem(UuidInterface $id): Item
    {
        foreach ($this->inventory as $key => $item) {
            if ($item->getId()->equals($id)) {
                $this->replenish($item->getResourceId());
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

    public function scavenge(ItemRepository $itemRepository): ?Item
    {
        $generator = (new Factory)->getMediumStrengthGenerator();

        $d100 = $generator->generateInt(1, 100);

        if ($d100 === 100) {
            $scavengedItem = $itemRepository->find(Uuid::fromString("08db1181-2bc9-4408-b378-5270e8dbee4b"));
            $this->addToInventory($scavengedItem);
        } elseif ($d100 === 99) {
            $scavengedItem = $itemRepository->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"));
            $this->addToInventory($scavengedItem);
        } else {

            $diceRoll = $generator->generateInt(1, 6);

            if ($diceRoll === 4 && $this->isAddictedToHeroin()) {
                $scavengedItem = $itemRepository->find(Uuid::fromString("275d6f62-16ff-4f5f-8ac6-149ec4cde1e2"));
                $this->addToInventory($scavengedItem);

            } elseif ($diceRoll === 5) {
                $scavengedItem = $itemRepository->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d"));
                $scavengedItem->add($generator->generateInt(0, 2));
                $this->addToInventory($scavengedItem);

            } elseif ($diceRoll === 6) {
                $scavengedItem = $itemRepository->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158"));
                $scavengedItem->add($generator->generateInt(0, 1));
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
        if (array_key_exists(strval($addedItem->getId()), $this->inventory)) {
            $this->inventory[strval($addedItem->getId())]->add($addedItem->getQuantity());
        } else {
            $this->inventory[strval($addedItem->getId())] = $addedItem;
        }
    }

    public function wait(): void
    {
        $this->consumeResources();
    }

    private function isAddictedToHeroin(): bool
    {
        return array_key_exists("5234c112-05be-4b15-80df-3c2b67e88262", $this->resourceLevels);
    }

    public function replenish(UuidInterface $resourceId): void
    {
        if ($resourceId->equals(Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"))
            && !$this->isAddictedToHeroin()
        ) {
            $this->consumeResources();

            $this->resourceLevels["5234c112-05be-4b15-80df-3c2b67e88262"] = new ResourceLevel(
                Uuid::fromString("5234c112-05be-4b15-80df-3c2b67e88262"),
                12,
                12
            );
        } else {
            foreach ($this->resourceLevels as $key => $resourceLevel) {
                if ($resourceLevel->getResourceId()->equals($resourceId)) {
                    $this->resourceLevels[$key] = $resourceLevel->replenish();
                } else {
                    $this->resourceLevels[$key] = $this->consumeResource($resourceLevel);
                }
            }
        }
    }

    private function consumeResources(): void
    {
        foreach ($this->resourceLevels as $key => $resourceLevel) {
            $this->resourceLevels[$key] = $this->consumeResource($resourceLevel);
        }
    }

    private function consumeResource(ResourceLevel $resourceLevel): ResourceLevel
    {
        $resourceLevel = $resourceLevel->consume();

        if ($resourceLevel->isDepleted()) {
            $this->isIntact = false;
        }

        return $resourceLevel;
    }

    public function reset(ItemRepository $itemRepository): void
    {
        $this->isIntact = true;

        $this->resourceLevels = [
            "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => new ResourceLevel(
                Uuid::fromString("6f5cc44d-db25-454a-b3fb-4ab3f61ce179"),
                3,
                0 // Hack
            ),
            "9972c015-842a-4601-8fb2-c900e1a54177" => new ResourceLevel(
                Uuid::fromString("9972c015-842a-4601-8fb2-c900e1a54177"),
                3,
                0 // Hack
            )
        ];

        $this->inventory = [
            $waterBottle = $itemRepository->find(Uuid::fromString("2f555296-ff9f-4205-a4f7-d181e4455f9d")),
            $sandwich = $itemRepository->find(Uuid::fromString("9c2bb508-c40f-491b-a4ca-fc811087a158")),
        ];

        $waterBottle->add(7);
        $sandwich->add(2);
    }
}
