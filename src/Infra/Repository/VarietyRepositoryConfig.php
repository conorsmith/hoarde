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
        "fde2146a-c29d-4262-b96f-ec7b696eccad" => [
            'label'       => "Human",
            'resourceId'  => "ab68e402-c4e9-43ef-a33b-cae0be014a09",
            'weight'      => 75000,
            'icon'        => "user",
            'description' => "Homo sapiens, the only extant members of the subtribe Hominina.",
        ],
        "2f555296-ff9f-4205-a4f7-d181e4455f9d" => [
            'label'       => "Water Bottle",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A bottle of water that is probably still drinkable.",
        ],
        "08db1181-2bc9-4408-b378-5270e8dbee4b" => [
            'label'       => "Coke Zero",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola!",
        ],
        "450349d4-fe21-4da0-8f78-99c684b05b45" => [
            'label'       => "Cherry Coke Zero",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with sweet cherry!",
        ],
        "813980ad-7604-4713-909c-b2701420de1b" => [
            'label'       => "Vanilla Coke Zero",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with creamy vanilla!",
        ],
        "e12981d2-5873-454a-b297-895f42e66bd5" => [
            'label'       => "Peach Coke Zero",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with overpowering peach!",
        ],
        "5de1c51c-2747-426d-a3b0-c854107c7132" => [
            'label'       => "Ginger Coke Zero",
            'resourceId'  => "9972c015-842a-4601-8fb2-c900e1a54177",
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, a real spicy boy!",
        ],
        "9c2bb508-c40f-491b-a4ca-fc811087a158" => [
            'label'       => "Tinned Stew",
            'resourceId'  => "6f5cc44d-db25-454a-b3fb-4ab3f61ce179",
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The faded label indicates it to be some variety of stew.",
        ],
        "cf057538-d3f0-4657-8a4c-f911bc113ad7" => [
            'label'       => "Tinned Drew",
            'resourceId'  => "6f5cc44d-db25-454a-b3fb-4ab3f61ce179",
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The label is thoroughly worn, but it indicates that the can contains... drew?",
        ],
        "275d6f62-16ff-4f5f-8ac6-149ec4cde1e2" => [
            'label'       => "Pringle",
            'resourceId'  => "5234c112-05be-4b15-80df-3c2b67e88262",
            'weight'      => 1,
            'icon'        => "moon",
            'description' => "A delicious stackable potato crisp, but be warned: once you pop, you cannot stop.",
        ],
        "59593b72-3845-491e-9721-4452a337019b" => [
            'label'       => "Wooden Crate",
            'resourceId'  => "e06a51c4-da54-4263-8d90-bc83fe126cd5",
            'weight'      => 4000,
            'icon'        => "box",
            'description' => "A sturdy crate crafted from wood in which items could be protected from the elements.",
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
            $this->resourceRepository->find(Uuid::fromString(self::VARIETIES[strval($id)]['resourceId'])),
            self::VARIETIES[strval($id)]['weight'],
            self::VARIETIES[strval($id)]['icon'],
            self::VARIETIES[strval($id)]['description']
        );
    }
}
