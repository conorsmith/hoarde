<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\Variety;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Domain\ResourceNeed;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Throwable;

final class EntityRepositoryDb implements EntityRepository
{
    private const RESOURCE_AMOUNTS_FOR_ENTITY = [
        ResourceRepositoryConfig::WATER    => 100,
        ResourceRepositoryConfig::FOOD     => 60,
        ResourceRepositoryConfig::PRINGLES => 1,
    ];

    /** @var Connection */
    private $db;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var array */
    private $cache;

    public function __construct(Connection $db, VarietyRepository $varietyRepository, ResourceRepository $resourceRepository)
    {
        $this->db = $db;
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
        $this->cache = [];
    }

    public function allInGame(UuidInterface $gameId): iterable
    {
        $rows = $this->db->fetchAll("SELECT * FROM entities WHERE game_id = :game_id ORDER BY order_index ASC", [
            'game_id' => $gameId,
        ]);

        $entities = [];

        foreach ($rows as $row) {
            $entities[] = $this->reconstituteEntity($row);
        }

        return $entities;
    }

    public function allInLocation(UuidInterface $locationId, UuidInterface $gameId): iterable
    {
        $rows = $this->db->fetchAll(
            "SELECT * FROM entities WHERE location_id = :location_id AND game_id = :game_id ORDER BY order_index ASC",
            [
                'location_id' => $locationId,
                'game_id' => $gameId,
            ]
        );

        $entities = [];

        foreach ($rows as $row) {
            $entities[] = $this->reconstituteEntity($row);
        }

        return $entities;
    }

    public function find(UuidInterface $id): ?Entity
    {
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = :id", [
            'id' => $id,
        ]);

        return $this->reconstituteEntity($row);
    }

    public function findInGame(UuidInterface $id, UuidInterface $gameId): ?Entity
    {
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = :id AND game_id = :game_id", [
            'id'      => $id,
            'game_id' => $gameId,
        ]);

        return $this->reconstituteEntity($row);
    }

    private function reconstituteEntity($row): ?Entity
    {
        if ($row === false) {
            return null;
        }

        if (array_key_exists($row['id'], $this->cache)) {
            return $this->cache[$row['id']];
        }

        $id = Uuid::fromString($row['id']);

        $variety = $this->varietyRepository->find(Uuid::fromString($row['variety_id']));

        $entity = new Entity(
            $id,
            Uuid::fromString($row['game_id']),
            $row['location_id']
                ? Uuid::fromString($row['location_id'])
                : Uuid::fromString("00000000-0000-0000-0000-000000000000"),
            Uuid::fromString($row['variety_id']),
            $row['label'],
            $row['icon'],
            intval($row['order_index']),
            $row['intact'] === "1",
            new Construction(
                $row['is_constructed'] === "1",
                intval($row['construction_level']),
                $variety->hasBlueprint() ? $variety->getBlueprint()->getTurns() : 0
            ),
            $this->findResourceNeeds($id, $variety),
            $variety->hasInventory() ? $this->findInventory($id, $variety) : null
        );

        $this->cache[$row['id']] = $entity;

        return $entity;
    }

    private function findResourceNeeds(UuidInterface $id, Variety $variety): iterable
    {
        $rows = $this->db->fetchAll("SELECT * FROM entity_resources WHERE entity_id = :id", [
            'id' => $id,
        ]);

        $resourceNeeds = [];

        foreach ($rows as $row) {
            $resourceNeeds[] = new ResourceNeed(
                $this->resourceRepository->find(Uuid::fromString($row['resource_id'])),
                intval($row['level']),
                $variety->getResourceNeedCapacities()[$row['resource_id']],
                self::RESOURCE_AMOUNTS_FOR_ENTITY[$row['resource_id']],
                is_null($row['last_consumed_variety_id'])
                    ? null
                    : Uuid::fromString($row['last_consumed_variety_id'])
            );
        }

        return $resourceNeeds;
    }

    private function findInventory(UuidInterface $id, Variety $variety): Inventory
    {
        $itemRows = $this->db->fetchAll("SELECT * FROM entity_inventory WHERE entity_id = :id", [
            'id' => $id,
        ]);

        $items = [];

        foreach ($itemRows as $row) {
            $item = $this->varietyRepository
                ->find(Uuid::fromString($row['item_id']))
                ->createItemWithQuantity(intval($row['quantity']));
            $items[] = $item;
        }

        usort($items, function (Item $itemA, Item $itemB) {
            return strnatcasecmp($itemA->getVariety()->getLabel(), $itemB->getVariety()->getLabel());
        });

        $entityRows = $this->db->fetchAll("SELECT * FROM entity_inventory_entities WHERE entity_id = :id", [
            'id' => $id,
        ]);

        $entities = [];

        foreach ($entityRows as $row) {
            $entities[] = $this->find(Uuid::fromString($row['inventory_entity_id']));
        }

        return new Inventory($id, $variety->getInventoryCapacity(), $items, $entities);
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
                'id'                 => $entity->getId(),
                'game_id'            => $entity->getGameId(),
                'location_id'        => $entity->getLocationId(),
                'variety_id'         => $entity->getVarietyId(),
                'label'              => $entity->getLabel(),
                'icon'               => $entity->getIcon(),
                'order_index'        => $entity->getOrderIndex(),
                'intact'             => $entity->isIntact() ? "1" : "0",
                'is_constructed'     => $entity->getConstruction()->isConstructed() ? "1" : "0",
                'construction_level' => $entity->getConstruction()->getRemainingSteps(),
            ]);
        } else {
            $this->db->update("entities", [
                'location_id'        => $entity->getLocationId(),
                'label'              => $entity->getLabel(),
                'icon'               => $entity->getIcon(),
                'order_index'        => $entity->getOrderIndex(),
                'intact'             => $entity->isIntact() ? "1" : "0",
                'is_constructed'     => $entity->getConstruction()->isConstructed() ? "1" : "0",
                'construction_level' => $entity->getConstruction()->getRemainingSteps(),
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
                'entity_id'                => $entity->getId(),
                'resource_id'              => $resourceNeed->getResource()->getId(),
                'level'                    => $resourceNeed->getCurrentLevel(),
                'last_consumed_variety_id' => $resourceNeed->getLastConsumedVarietyId(),
            ]);
        }
    }

    private function saveInventory(Entity $entity): void
    {
        $this->db->delete("entity_inventory", [
            'entity_id' => strval($entity->getId()),
        ]);

        $this->db->delete("entity_inventory_entities", [
            'entity_id' => $entity->getId(),
        ]);

        if ($entity->hasInventory()) {
            foreach ($entity->getInventory()->getItems() as $item) {
                $this->db->insert("entity_inventory", [
                    'entity_id' => $entity->getId(),
                    'item_id'   => $item->getVariety()->getId(),
                    'quantity'  => $item->getQuantity(),
                ]);
            }

            foreach ($entity->getInventory()->getEntities() as $inventoryEntity) {
                $this->db->insert("entity_inventory_entities", [
                    'entity_id'           => $entity->getId(),
                    'inventory_entity_id' => $inventoryEntity->getId(),
                ]);
            }
        }
    }

    public function delete(Entity $entity): void
    {
        $this->db->beginTransaction();

        try {
            $this->deleteEntity($entity);
            $this->deleteResourceNeeds($entity);
            $this->deleteInventory($entity);

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function deleteEntity(Entity $entity): void
    {
        $this->db->delete("entities", [
            'id' => $entity->getId(),
        ]);
    }

    private function deleteResourceNeeds(Entity $entity): void
    {
        $this->db->delete("entity_resources", [
            'entity_id' => $entity->getId(),
        ]);
    }

    private function deleteInventory(Entity $entity): void
    {
        $this->db->delete("entity_inventory", [
            'entity_id' => $entity->getId(),
        ]);
    }
}
