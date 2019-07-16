<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Action;
use ConorSmith\Hoarde\Domain\ActionRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ActionRepositoryConfig implements ActionRepository
{
    public const CONSTRUCT = "26105a98-d85e-4ecc-9c08-d9027174ab63";
    public const CONSUME = "427d031d-ee80-4452-bbb3-5d2d96ca554b";
    public const DIG = "0e2bb910-26fa-4832-8fb0-5cb4efb69aba";
    public const HARVEST_FOOD = "75f64f5e-0ac8-45cb-9514-2071b28f37e6";
    public const HARVEST_SEEDS = "24e9dd2d-04f2-4bef-b235-17e6731376bd";
    public const PLACE = "2afdf3f4-b77e-4391-a857-fab631a8c2be";
    public const READ = "a5acf215-c719-40af-ac15-c38917735875";
    public const SOW = "99b65213-9cee-42ec-9dfe-8a04a790469e";

    private const CONFIG = [
        self::CONSTRUCT => [
            'label'               => "Construct",
            'icon'                => "tools",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::CONSUME   => [
            'label'               => "Consume",
            'icon'                => "drumstick-bite",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::DIG       => [
            'label'               => "Dig",
            'icon'                => "tools",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::HARVEST_FOOD   => [
            'label'               => "Harvest Food",
            'icon'                => "seedling",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::HARVEST_SEEDS   => [
            'label'               => "Harvest Seeds",
            'icon'                => "seedling",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::PLACE     => [
            'label'               => "Place",
            'icon'                => "people-carry",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::READ      => [
            'label'               => "Read",
            'icon'                => "book",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::SOW       => [
            'label'               => "Sow",
            'icon'                => "seedling",
            'performingVarieties' => [],
        ],
    ];

    public function all(): iterable
    {
        $actions = [];

        foreach (self::CONFIG as $id => $config) {
            $actions[] = $this->find(Uuid::fromString($id));
        }

        return $actions;
    }

    public function find(UuidInterface $id): ?Action
    {
        if (!array_key_exists(strval($id), self::CONFIG)) {
            return null;
        }

        return new Action(
            $id,
            self::CONFIG[strval($id)]['label'],
            self::CONFIG[strval($id)]['icon'],
            array_map(
                function (string $id) {
                    return Uuid::fromString($id);
                },
                self::CONFIG[strval($id)]['performingVarieties']
            )
        );
    }
}
