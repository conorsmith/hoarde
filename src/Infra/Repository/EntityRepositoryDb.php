<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    private const CONSTRUCTION_MAXIMUMS_FOR_ENTITY = [
        VarietyRepositoryConfig::WELL => 10,
    ];

    /** @var Connection */
    private $db;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(Connection $db, VarietyRepository $varietyRepository, ResourceRepository $resourceRepository)
    {
        $this->db = $db;
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
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
            Uuid::fromString($row['variety_id']),
            $row['label'],
            $row['icon'],
            $row['intact'] === "1",
            new Construction(
                $row['is_constructed'] === "1",
                intval($row['construction_level']),
                array_key_exists($row['variety_id'], self::CONSTRUCTION_MAXIMUMS_FOR_ENTITY)
                    ? self::CONSTRUCTION_MAXIMUMS_FOR_ENTITY[$row['variety_id']]
                    : 0
            ),
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
                $this->resourceRepository->find(Uuid::fromString($row['resource_id'])),
                intval($row['level']),
                self::RESOURCE_MAXIMUMS_FOR_ENTITY[$row['resource_id']],
                is_null($row['last_consumed_variety_id'])
                    ? null
                    : Uuid::fromString($row['last_consumed_variety_id'])
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
            $item = $this->varietyRepository
                ->find(Uuid::fromString($row['item_id']))
                ->createItemWithQuantity(intval($row['quantity']));
            $inventory[] = $item;
        }

        usort($inventory, function (Item $itemA, Item $itemB) {
            return strnatcasecmp($itemA->getVariety()->getLabel(), $itemB->getVariety()->getLabel());
        });

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
                'id'                 => $entity->getId(),
                'game_id'            => $entity->getGameId(),
                'variety_id'         => $entity->getVarietyId(),
                'label'              => $entity->getLabel(),
                'icon'               => $entity->getIcon(),
                'intact'             => $entity->isIntact(),
                'is_constructed'     => $entity->getConstruction()->isConstructed() ? "1" : "0",
                'construction_level' => $entity->getConstruction()->getRemainingSteps(),
            ]);
        } else {
            $this->db->update("entities", [
                'label'              => $entity->getLabel(),
                'icon'               => $entity->getIcon(),
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

        foreach ($entity->getInventory() as $item) {
            $this->db->insert("entity_inventory", [
                'entity_id' => $entity->getId(),
                'item_id'   => $item->getVariety()->getId(),
                'quantity'  => $item->getQuantity(),
            ]);
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
