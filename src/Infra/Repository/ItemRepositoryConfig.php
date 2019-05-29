<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\ItemRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ItemRepositoryConfig implements ItemRepository
{
    private const ITEMS = [
        "2f555296-ff9f-4205-a4f7-d181e4455f9d" => [
            'label'      => "Water Bottle",
            'resourceId' => "9972c015-842a-4601-8fb2-c900e1a54177",
        ],
        "9c2bb508-c40f-491b-a4ca-fc811087a158" => [
            'label'      => "Sandwich",
            'resourceId' => "6f5cc44d-db25-454a-b3fb-4ab3f61ce179",
        ],
    ];

    public function find(UuidInterface $id): ?Item
    {
        if (!array_key_exists(strval($id), self::ITEMS)) {
            return null;
        }

        return new Item(
            $id,
            self::ITEMS[strval($id)]['label'],
            1,
            Uuid::fromString(self::ITEMS[strval($id)]['resourceId'])
        );

    }
}
