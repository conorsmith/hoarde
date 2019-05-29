<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\ItemRepository;
use ConorSmith\Hoarde\Domain\ResourceNeed;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class EntityRepositoryDb implements EntityRepository
{
    private const RESOURCE_MAXIMUMS_FOR_ENTITY = [
        "9972c015-842a-4601-8fb2-c900e1a54177" => 5,
        "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => 10,
        "5234c112-05be-4b15-80df-3c2b67e88262" => 12,
    ];

    /** @var Connection */
    private $db;

    /** @var ItemRepository */
    private $itemRepository;

    public function __construct(Connection $db, ItemRepository $itemRepository)
    {
        $this->db = $db;
        $this->itemRepository = $itemRepository;
    }

    public function find(UuidInterface $id): ?Entity
    {
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = :id", [
            'id' => $id,
        ]);

        if ($row === false) {
            return null;
        }

        return new Entity(
            $id,
            Uuid::fromString($row['game_id']),
            $row['intact'] === "1",
            $this->findResourceNeeds($id),
            $this->findInventory($id)
        );
    }

    private function findResourceNeeds(UuidInterface $id): iterable
    {
        $rows = $this->db->fetchAll("SELECT * FROM entity_resources WHERE entity_id = :id", [
            'id' => $id,
        ]);

        $resourceNeeds = [];

        foreach ($rows as $row) {
            $resourceNeeds[] = new ResourceNeed(
                Uuid::fromString($row['resource_id']),
                intval($row['level']),
                self::RESOURCE_MAXIMUMS_FOR_ENTITY[$row['resource_id']]
            );
        }

        return $resourceNeeds;
    }

    private function findInventory(UuidInterface $id): iterable
    {
        $itemRows = $this->db->fetchAll("SELECT * FROM entity_inventory WHERE entity_id = :id", [
            'id' => $id,
        ]);

        $inventory = [];

        foreach ($itemRows as $row) {
            $item = $this->itemRepository->find(Uuid::fromString($row['item_id']));
            $item->add(intval($row['quantity']) - 1);
            $inventory[] = $item;
        }

        return $inventory;
    }

    public function save(Entity $entity): void
    {
        $this->db->beginTransaction();

        try {
            $this->saveEntity($entity);
            $this->saveResourceNeeds($entity);
            $this->saveInventory($entity);

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function saveEntity(Entity $entity): void
    {
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = :id", [
            'id' => $entity->getId(),
        ]);

        if ($row === false) {
            $this->db->insert("entities", [
                'id'      => $entity->getId(),
                'game_id' => $entity->getGameId(),
                'intact'  => $entity->isIntact(),
            ]);
        } else {
            $this->db->update("entities", [
                'intact' => $entity->isIntact() ? "1" : "0",
            ], [
                'id' => $entity->getId(),
            ]);
        }
    }

    private function saveResourceNeeds(Entity $entity): void
    {
        $this->db->delete("entity_resources", [
            'entity_id' => $entity->getId(),
        ]);

        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $this->db->insert("entity_resources", [
                'entity_id'   => $entity->getId(),
                'resource_id' => $resourceNeed->getResourceId(),
                'level'       => $resourceNeed->getCurrentLevel(),
            ]);
        }
    }

    private function saveInventory(Entity $entity): void
    {
        $this->db->delete("entity_inventory", [
            'entity_id' => strval($entity->getId()),
        ]);

        foreach ($entity->getInventory() as $item) {
            $this->db->insert("entity_inventory", [
                'entity_id' => $entity->getId(),
                'item_id'   => $item->getId(),
                'quantity'  => $item->getQuantity(),
            ]);
        }
    }
}
