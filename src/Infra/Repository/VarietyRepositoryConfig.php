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
    public const HUMAN = "fde2146a-c29d-4262-b96f-ec7b696eccad";
    public const WATER_BOTTLE = "2f555296-ff9f-4205-a4f7-d181e4455f9d";
    public const COKE_ZERO = "08db1181-2bc9-4408-b378-5270e8dbee4b";
    public const CHERRY_COKE_ZERO = "450349d4-fe21-4da0-8f78-99c684b05b45";
    public const VANILLA_COKE_ZERO = "813980ad-7604-4713-909c-b2701420de1b";
    public const PEACH_COKE_ZERO = "e12981d2-5873-454a-b297-895f42e66bd5";
    public const GINGER_COKE_ZERO = "5de1c51c-2747-426d-a3b0-c854107c7132";
    public const TINNED_STEW = "9c2bb508-c40f-491b-a4ca-fc811087a158";
    public const TINNED_DREW = "cf057538-d3f0-4657-8a4c-f911bc113ad7";
    public const TINNED_SOUP = "fb793da2-cff9-4e88-9f9c-84278c6662ca";
    public const PRINGLE = "275d6f62-16ff-4f5f-8ac6-149ec4cde1e2";
    public const WOODEN_CRATE = "59593b72-3845-491e-9721-4452a337019b";

    private const VARIETIES = [
        self::HUMAN => [
            'label'       => "Human",
            'resources'   => [],
            'weight'      => 75000,
            'icon'        => "user",
            'description' => "Homo sapiens, the only extant members of the subtribe Hominina.",
        ],
        self::WATER_BOTTLE => [
            'label'       => "Water Bottle",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A bottle of water that is probably still drinkable.",
        ],
        self::COKE_ZERO => [
            'label'       => "Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola!",
        ],
        self::CHERRY_COKE_ZERO => [
            'label'       => "Cherry Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with sweet cherry!",
        ],
        self::VANILLA_COKE_ZERO => [
            'label'       => "Vanilla Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with creamy vanilla!",
        ],
        self::PEACH_COKE_ZERO => [
            'label'       => "Peach Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, now flavoured with overpowering peach!",
        ],
        self::GINGER_COKE_ZERO => [
            'label'       => "Ginger Coke Zero",
            'resources'   => [
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 500,
            'icon'        => "tint",
            'description' => "A refreshing sugar-free cola, a real spicy boy!",
        ],
        self::TINNED_STEW => [
            'label'       => "Tinned Stew",
            'resources'   => [
                ResourceRepositoryConfig::FOOD,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The faded label indicates it to be some variety of stew.",
        ],
        self::TINNED_DREW => [
            'label'       => "Tinned Drew",
            'resources'   => [
                ResourceRepositoryConfig::FOOD,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The label is thoroughly worn, but it indicates that the can contains... drew?",
        ],
        self::TINNED_SOUP => [
            'label'       => "Tinned Soup",
            'resources'   => [
                ResourceRepositoryConfig::FOOD,
                ResourceRepositoryConfig::WATER,
            ],
            'weight'      => 600,
            'icon'        => "utensils",
            'description' => "A steel can for storing food. The faded label indicates it to be some variety of soup.",
        ],
        self::PRINGLE => [
            'label'       => "Pringle",
            'resources'   => [
                ResourceRepositoryConfig::PRINGLES,
            ],
            'weight'      => 1,
            'icon'        => "moon",
            'description' => "A delicious stackable potato crisp, but be warned: once you pop, you cannot stop.",
        ],
        self::WOODEN_CRATE => [
            'label'       => "Wooden Crate",
            'resources'   => [
                ResourceRepositoryConfig::STORAGE,
            ],
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
            array_map(function (string $resourceId) {
                return $this->resourceRepository->find(Uuid::fromString($resourceId));
            }, self::VARIETIES[strval($id)]['resources']),
            self::VARIETIES[strval($id)]['weight'],
            self::VARIETIES[strval($id)]['icon'],
            self::VARIETIES[strval($id)]['description']
        );
    }
}
