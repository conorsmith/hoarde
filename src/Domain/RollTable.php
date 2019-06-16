<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Ramsey\Uuid\Uuid;
use RandomLib\Factory;
use RandomLib\Generator;

class RollTable
{
    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var Generator */
    private $generator;

    public function __construct(VarietyRepository $varietyRepository)
    {
        $this->varietyRepository = $varietyRepository;
        $this->generator = (new Factory)->getLowStrengthGenerator();
    }

    public function forEntity(Entity $entity, int $length): iterable
    {
        if ($length === 3) {
            $rollTable = $this->getLongTable();
        } else {
            $rollTable = $this->getShortTable();
        }

        $rollTable[] = [
            'rolls' => range(1, 1000),
            'item'  => $this->varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::RADISH_SEED)),
            'quantity' => [100, 100],
        ];

        $rollTable[] = [
            'rolls' => range(1, 1000),
            'item'  => $this->varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::ROPE)),
        ];

        $rollTable[] = [
            'rolls' => range(1, 1000),
            'item'  => $this->varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::SHOVEL)),
        ];

        if ($entity->needsPringles()) {
            return $this->appendPringles($rollTable);
        } else {
            return $rollTable;
        }
    }

    private function getLongTable(): array
    {
        return [
            [
                'rolls' => range(990, 1000),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE)),
            ],
            [
                'rolls' => range(980, 989),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::COKE_ZERO)),
            ],
            [
                'rolls' => range(970, 979),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PRINGLE)),
                'quantity' => [1, 3],
            ],
            [
                'rolls' => range(965, 969),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::CHERRY_COKE_ZERO)),
            ],
            [
                'rolls' => range(960, 964),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::VANILLA_COKE_ZERO)),
            ],
            [
                'rolls' => range(955, 959),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PEACH_COKE_ZERO)),
            ],
            [
                'rolls' => range(950, 954),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::GINGER_COKE_ZERO)),
            ],
            [
                'rolls' => range(940, 944),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_DREW)),
            ],
            [
                'rolls' => range(921, 930),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::BUCKET)),
            ],
            [
                'rolls' => range(911, 920),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::ROPE)),
            ],
            [
                'rolls' => range(901, 910),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::SHOVEL)),
            ],
            [
                'rolls' => range(891, 900),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::HAND_SAW)),
            ],
            [
                'rolls' => range(881, 890),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::HAMMER)),
            ],
            [
                'rolls' => [813],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_13)),
            ],
            [
                'rolls' => [812],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_12)),
            ],
            [
                'rolls' => [811],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_11)),
            ],
            [
                'rolls' => [810],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_10)),
            ],
            [
                'rolls' => [809],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_9)),
            ],
            [
                'rolls' => [808],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_8)),
            ],
            [
                'rolls' => [807],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_7)),
            ],
            [
                'rolls' => [806],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_6)),
            ],
            [
                'rolls' => [805],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_5)),
            ],
            [
                'rolls' => [804],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_4)),
            ],
            [
                'rolls' => [803],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_3)),
            ],
            [
                'rolls' => [802],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_2)),
            ],
            [
                'rolls' => [801],
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_1)),
            ],
            [
                'rolls' => range(421, 450),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::NAIL)),
                'quantity' => [3, 11],
            ],
            [
                'rolls' => range(401, 430),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TIMBER)),
                'quantity' => [1, 4],
            ],
            [
                'rolls' => array_merge(range(1, 10), range(290, 330)),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_SOUP)),
                'quantity' => [2, 4],
            ],
            [
                'rolls' => range(140, 300),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE)),
                'quantity' => [2, 6],
            ],
            [
                'rolls' => range(1, 170),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW)),
                'quantity' => [2, 5],
            ],
        ];
    }

    private function getShortTable(): array
    {
        return [
            [
                'rolls' => range(996, 1000),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE)),
            ],
            [
                'rolls' => range(980, 989),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::COKE_ZERO)),
            ],
            [
                'rolls' => range(970, 979),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PRINGLE)),
            ],
            [
                'rolls' => range(965, 969),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::CHERRY_COKE_ZERO)),
            ],
            [
                'rolls' => range(960, 964),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::VANILLA_COKE_ZERO)),
            ],
            [
                'rolls' => range(955, 959),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::PEACH_COKE_ZERO)),
            ],
            [
                'rolls' => range(950, 954),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::GINGER_COKE_ZERO)),
            ],
            [
                'rolls' => range(940, 944),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_DREW)),
            ],
            [
                'rolls' => range(914, 916),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::HAND_SAW)),
            ],
            [
                'rolls' => range(911, 913),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::HAMMER)),
            ],
            [
                'rolls' => range(907, 909),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::BUCKET)),
            ],
            [
                'rolls' => range(904, 906),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::ROPE)),
            ],
            [
                'rolls' => range(901, 903),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::SHOVEL)),
            ],
            [
                'rolls' => range(804, 804),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_11)),
            ],
            [
                'rolls' => range(803, 803),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_8)),
            ],
            [
                'rolls' => range(802, 802),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_4)),
            ],
            [
                'rolls' => range(801, 801),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::DRIL_FIGURINE_3)),
            ],
            [
                'rolls' => range(421, 450),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::NAIL)),
                'quantity' => [2, 6],
            ],
            [
                'rolls' => range(401, 422),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TIMBER)),
                'quantity' => [1, 2],
            ],
            [
                'rolls' => array_merge(range(1, 10), range(290, 330)),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_SOUP)),
                'quantity' => [1, 2],
            ],
            [
                'rolls' => range(140, 300),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE)),
                'quantity' => [1, 3],
            ],
            [
                'rolls' => range(1, 170),
                'item'  => $this->varietyRepository
                    ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW)),
                'quantity' => [1, 2],
            ],
        ];
    }

    private function appendPringles(array $rollTable): array
    {
        $rollTable[] = [
            'rolls' => range(350, 510),
            'item'  => $this->varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::PRINGLE)),
        ];

        return $rollTable;
    }
}
