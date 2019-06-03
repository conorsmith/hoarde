<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Ramsey\Uuid\UuidInterface;

final class ResourceRepositoryConfig implements ResourceRepository
{
    public const WATER = "9972c015-842a-4601-8fb2-c900e1a54177";
    public const FOOD = "6f5cc44d-db25-454a-b3fb-4ab3f61ce179";
    public const PRINGLES = "5234c112-05be-4b15-80df-3c2b67e88262";
    public const STORAGE = "e06a51c4-da54-4263-8d90-bc83fe126cd5";

    private const RESOURCES = [
        self::WATER    => "Water",
        self::FOOD     => "Food",
        self::PRINGLES => "Pringles",
        self::STORAGE  => "Storage",
    ];

    public function find(UuidInterface $id): ?Resource
    {
        if (!array_key_exists(strval($id), self::RESOURCES)) {
            return null;
        }

        return new Resource($id, self::RESOURCES[strval($id)]);
    }
}
