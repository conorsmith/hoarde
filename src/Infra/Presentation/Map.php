<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Coordinates;
use ConorSmith\Hoarde\Domain\Map as DomainModel;
use stdClass;

final class Map
{
    public function __construct(DomainModel $map)
    {
        $this->rows = [];

        $i = 0;

        foreach ($map->getCoordinatesGroupedByYPosition() as $coordinatesGroup) {
            $this->rows[$i] = [];
            foreach ($coordinatesGroup as $coordinates) {
                $this->rows[$i][] = $this->presentLocation($coordinates, $map);
            }
            $i++;
        }
    }

    private function presentLocation(Coordinates $coordinates, DomainModel $map): stdClass
    {
        return (object) [
            'id'      => $map->hasLocation($coordinates)
                ? strval($map->findLocation($coordinates)->getId())
                : null,
            'x'       => $coordinates->getX(),
            'y'       => $coordinates->getY(),
            'isKnown' => $map->hasLocation($coordinates),
            'icon'    => $this->presentIcon($coordinates, $map),
        ];
    }

    private function presentIcon(Coordinates $coordinates, DomainModel $map): string
    {
        $entities = $map->findNotableEntities($coordinates);

        if (count($entities) === 0) {
            return "";
        }

        $entity = array_pop($entities);

        return "fa-" . $entity->getIcon();
    }
}
