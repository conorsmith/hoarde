<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class LocationTemplate
{
    /** @var Location */
    private $location;

    /** @var iterable */
    private $entities;

    public function __construct(Location $location, iterable $entities)
    {
        $this->location = $location;
        $this->entities = $entities;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function getEntities(): iterable
    {
        return $this->entities;
    }
}
