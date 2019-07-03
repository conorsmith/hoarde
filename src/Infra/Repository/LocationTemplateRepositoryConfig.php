<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Coordinates;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationTemplate;
use ConorSmith\Hoarde\Domain\LocationTemplateRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class LocationTemplateRepositoryConfig implements LocationTemplateRepository
{
    private const CONFIG = [
        "0,2" => [
            'entities' => [
                [
                    'varietyId' => VarietyRepositoryConfig::TOOLBOX,
                    'inventory' => [
                        [
                            'varietyId' => VarietyRepositoryConfig::JOELS_NOTE,
                            'quantity'  => 1,
                        ],
                        [
                            'varietyId' => VarietyRepositoryConfig::TINNED_SOUP,
                            'quantity'  => 1,
                        ],
                    ],
                ],
            ],
        ],
    ];

    /** @var VarietyRepository */
    private $varietyRepository;

    public function __construct(VarietyRepository $varietyRepository)
    {
        $this->varietyRepository = $varietyRepository;
    }

    public function generateNewLocation(Coordinates $coordinates, UuidInterface $gameId): LocationTemplate
    {
        $key = "{$coordinates->getX()},{$coordinates->getY()}";

        if (!array_key_exists($key, self::CONFIG)) {
            return new LocationTemplate(
                new Location(
                    Uuid::uuid4(),
                    $gameId,
                    $coordinates,
                    5
                ),
                []
            );
        }

        $config = self::CONFIG[$key];

        $locationId = Uuid::uuid4();

        $generatedEntities = [];

        foreach ($config['entities'] as $entityConfig) {
            $variety = $this->varietyRepository->find(Uuid::fromString($entityConfig['varietyId']));

            $entity = new Entity(
                $entityId = Uuid::uuid4(),
                $gameId,
                $locationId,
                $variety->getId(),
                $variety->getLabel(),
                $variety->getIcon(),
                1,
                true,
                Construction::constructed(),
                [],
                Inventory::empty($entityId, $variety)
            );

            foreach ($entityConfig['inventory'] as $itemConfig) {
                $entity->getInventory()->addItem(
                    $this->varietyRepository->find(Uuid::fromString($itemConfig['varietyId']))
                        ->createItemWithQuantity($itemConfig['quantity'])
                );
            }

            $generatedEntities[] = $entity;
        }

        return new LocationTemplate(
            new Location(
                $locationId,
                $gameId,
                $coordinates,
                5
            ),
            $generatedEntities
        );
    }
}
