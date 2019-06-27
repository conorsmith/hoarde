<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Location as DomainModel;

final class Location
{
    public function __construct(DomainModel $location)
    {
        $this->coordinates = "{$location->getCoordinates()->getX()}, {$location->getCoordinates()->getY()}";
        $this->remainingScavengingLevel = $location->getScavengingLevel() / 5 * 100;
    }
}
