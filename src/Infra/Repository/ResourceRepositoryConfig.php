<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Ramsey\Uuid\UuidInterface;

final class ResourceRepositoryConfig implements ResourceRepository
{
    private const RESOURCES = [
        "9972c015-842a-4601-8fb2-c900e1a54177" => "Water",
        "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => "Food",
        "5234c112-05be-4b15-80df-3c2b67e88262" => "Pringles",
        "e06a51c4-da54-4263-8d90-bc83fe126cd5" => "Wood",
        "ab68e402-c4e9-43ef-a33b-cae0be014a09" => "Meat",
    ];

    public function find(UuidInterface $id): ?Resource
    {
        if (!array_key_exists(strval($id), self::RESOURCES)) {
            return null;
        }

        return new Resource($id, self::RESOURCES[strval($id)]);
    }
}
