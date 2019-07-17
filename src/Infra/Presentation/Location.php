<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Location as DomainModel;
use ConorSmith\Hoarde\Domain\Map;
use ConorSmith\Hoarde\Infra\Repository\BiomeRepositoryConfig;
use Ramsey\Uuid\UuidInterface;

final class Location
{
    public function __construct(DomainModel $location, Map $map)
    {
        $this->coordinates = "{$location->getCoordinates()->getX()}, {$location->getCoordinates()->getY()}";
        $this->remainingScavengingLevel = $location->getScavengingLevel() / $this->getMaximumScavengingLevel($location->getBiomeId()) * 100;
        $this->entitiesCount = count($map->findNotableEntities($location->getCoordinates()));
    }

    private function getMaximumScavengingLevel(UuidInterface $biomeId): int
    {
        switch ($biomeId->toString()) {
            case BiomeRepositoryConfig::OCEAN:
                return 0;
            case BiomeRepositoryConfig::ARABLE:
                return 5;
            case BiomeRepositoryConfig::URBAN:
                return 25;
        }
    }
}
