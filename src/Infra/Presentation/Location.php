<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Location as DomainModel;
use ConorSmith\Hoarde\Domain\Map;

final class Location
{
    public function __construct(DomainModel $location, Map $map)
    {
        $this->coordinates = "{$location->getCoordinates()->getX()}, {$location->getCoordinates()->getY()}";
        $this->remainingScavengingLevel = $location->getScavengingLevel() / 5 * 100;
        $this->entitiesCount = count($map->findNotableEntities($location->getCoordinates()));
    }
}
