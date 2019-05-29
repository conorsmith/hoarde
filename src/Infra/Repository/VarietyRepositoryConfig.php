<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\Variety;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class VarietyRepositoryConfig implements VarietyRepository
{
    private const VARIETIES = [
        "2f555296-ff9f-4205-a4f7-d181e4455f9d" => [
            'label'      => "Water Bottle",
            'resourceId' => "9972c015-842a-4601-8fb2-c900e1a54177",
        ],
        "9c2bb508-c40f-491b-a4ca-fc811087a158" => [
            'label'      => "Sandwich",
            'resourceId' => "6f5cc44d-db25-454a-b3fb-4ab3f61ce179",
        ],
        "08db1181-2bc9-4408-b378-5270e8dbee4b" => [
            'label'      => "Coke Zero",
            'resourceId' => "9972c015-842a-4601-8fb2-c900e1a54177",
        ],
        "275d6f62-16ff-4f5f-8ac6-149ec4cde1e2" => [
            'label'      => "Heroin",
            'resourceId' => "5234c112-05be-4b15-80df-3c2b67e88262",
        ],
    ];

    private $resourceRepository;

    public function __construct(ResourceRepository $resourceRepository)
    {
        $this->resourceRepository = $resourceRepository;
    }

    public function find(UuidInterface $id): ?Variety
    {
        if (!array_key_exists(strval($id), self::VARIETIES)) {
            return null;
        }

        return new Variety(
            $id,
            self::VARIETIES[strval($id)]['label'],
            $this->resourceRepository->find(Uuid::fromString(self::VARIETIES[strval($id)]['resourceId']))
        );
    }
}
