<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\ItemRepository;
use ConorSmith\Hoarde\Domain\ResourceLevel;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class EntityRepositoryDb implements EntityRepository
{
    private const RESOURCE_MAXIMUMS = [
        "9972c015-842a-4601-8fb2-c900e1a54177" => 5,
        "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => 10,
    ];

    private const ITEMS = [
        "2f555296-ff9f-4205-a4f7-d181e4455f9d" => [
            'label' => "Water Bottle",
            'resourceId' => "9972c015-842a-4601-8fb2-c900e1a54177",
        ],
        "9c2bb508-c40f-491b-a4ca-fc811087a158" => [
            'label' => "Sandwich",
            'resourceId' => "6f5cc44d-db25-454a-b3fb-4ab3f61ce179",
        ],
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
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = ?", [
            strval($id),
        ]);

        if ($row === false) {
            return null;
        }

        $resourceRows = $this->db->fetchAll("SELECT * FROM entity_resources WHERE entity_id = ?", [
            strval($id),
        ]);

        if (count($resourceRows) === 0) {
            return null;
        }

        $resourceLevels = [];

        foreach ($resourceRows as $resourceRow) {
            $resourceLevels[] = new ResourceLevel(
                Uuid::fromString($resourceRow['resource_id']),
                intval($resourceRow['level']),
                self::RESOURCE_MAXIMUMS[$resourceRow['resource_id']]
            );
        }

        $itemRows = $this->db->fetchAll("SELECT * FROM entity_inventory WHERE entity_id = ?", [
            strval($id),
        ]);

        $inventory = [];

        foreach ($itemRows as $itemRow) {
            $item = $this->itemRepository->find(Uuid::fromString($itemRow['item_id']));
            $item->add(intval($itemRow['quantity']) - 1);
            $inventory[] = $item;
        }

        return new Entity(
            $id,
            Uuid::fromString($row['game_id']),
            $row['intact'] === "1",
            $resourceLevels,
            $inventory
        );
    }

    public function save(Entity $entity): void
    {
        $row = $this->db->fetchAssoc("SELECT * FROM entities WHERE id = :id", [
            'id' => strval($entity->getId()),
        ]);

        if ($row === false) {
            $this->db->insert("entities", [
                'id'      => $entity->getId(),
                'game_id' => $entity->getGameId(),
                'intact'  => $entity->isIntact(),
            ]);

            foreach ($entity->getResourceLevels() as $resourceLevel) {
                $this->db->insert("entity_resources", [
                    'entity_id' => $entity->getId(),
                    'resource_id' => $resourceLevel->getResourceId(),
                    'level' => $resourceLevel->getValue(),
                ]);
            }

        } else {
            $this->db->update("entities", [
                'intact' => $entity->isIntact() ? "1" : "0",
            ], [
                'id' => strval($entity->getId()),
            ]);

            foreach ($entity->getResourceLevels() as $resourceLevel) {
                $this->db->update("entity_resources", [
                    'level' => $resourceLevel->getValue(),
                ], [
                    'entity_id'   => strval($entity->getId()),
                    'resource_id' => strval($resourceLevel->getResourceId()),
                ]);
            }
        }

        $this->db->delete("entity_inventory", [
            'entity_id' => strval($entity->getId()),
        ]);

        foreach ($entity->getInventory() as $item) {
            $this->db->insert("entity_inventory", [
                'entity_id' => strval($entity->getId()),
                'item_id'   => strval($item->getId()),
                'quantity'  => $item->getQuantity(),
            ]);
        }
    }
}
