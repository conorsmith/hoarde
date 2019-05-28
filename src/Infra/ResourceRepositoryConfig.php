<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Ramsey\Uuid\UuidInterface;

final class ResourceRepositoryConfig implements ResourceRepository
{
    private const RESOURCES = [
        "9972c015-842a-4601-8fb2-c900e1a54177" => "Water",
        "6f5cc44d-db25-454a-b3fb-4ab3f61ce179" => "Food",
    ];

    public function find(UuidInterface $id): ?Resource
    {
        if (!array_key_exists(strval($id), self::RESOURCES)) {
            return null;
        }

        return new Resource($id, self::RESOURCES[strval($id)]);
    }
}
