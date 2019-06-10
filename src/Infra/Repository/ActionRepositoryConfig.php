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
    public const PLACE = "2afdf3f4-b77e-4391-a857-fab631a8c2be";

    private const CONFIG = [
        self::CONSTRUCT   => [
            'label'               => "Construct",
            'icon'                => "tools",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::CONSUME => [
            'label'               => "Consume",
            'icon'                => "drumstick-bite",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::DIG     => [
            'label'               => "Dig",
            'icon'                => "tools",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
        ],
        self::PLACE   => [
            'label'               => "Place",
            'icon'                => "people-carry",
            'performingVarieties' => [
                VarietyRepositoryConfig::HUMAN,
            ],
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
