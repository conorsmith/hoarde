<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Map
{
    private static function createKey(Coordinates $coordinates): string
    {
        return "{$coordinates->getX()},{$coordinates->getY()}";
    }

    /** @var array */
    private $setOfCoordinates;

    /** @var array */
    private $locations;

    /** @var array */
    private $entitiesByLocation;

    public function __construct(iterable $setOfCoordinates, iterable $locations, iterable $entities)
    {
        $this->setOfCoordinates = [];
        $this->locations = [];
        $this->entitiesByLocation = [];

        foreach ($setOfCoordinates as $coordinates) {
            if (!$coordinates instanceof Coordinates) {
                throw new DomainException;
            }

            $this->setOfCoordinates[] = $coordinates;
        }

        foreach ($locations as $location) {
            if (!$location instanceof Location) {
                throw new DomainException;
            }

            $this->locations[self::createKey($location->getCoordinates())] = $location;
        }

        foreach ($entities as $entity) {
            if (!$entity instanceof Entity) {
                throw new DomainException;
            }

            if ($this->hasLocationById($entity->getLocationId())) {
                $location = $this->findLocationById($entity->getLocationId());
                $key = self::createKey($location->getCoordinates());

                if (!array_key_exists($key, $this->entitiesByLocation)) {
                    $this->entitiesByLocation[$key] = [];
                }

                $this->entitiesByLocation[$key][] = $entity;
            }
        }
    }

    public function getCoordinatesGroupedByYPosition(): iterable
    {
        $groups = [];

        foreach ($this->setOfCoordinates as $coordinates) {
            if (!array_key_exists($coordinates->getY(), $groups)) {
                $groups[$coordinates->getY()] = [];
            }

            $groups[$coordinates->getY()][] = $coordinates;
        }

        return $groups;
    }

    public function hasLocation(Coordinates $coordinates): bool
    {
        return array_key_exists(self::createKey($coordinates), $this->locations);
    }

    public function findLocation(Coordinates $coordinates): Location
    {
        return $this->locations[self::createKey($coordinates)];
    }

    public function findNotableEntities(Coordinates $coordinates): iterable
    {
        $entities = [];

        if (!array_key_exists(self::createKey($coordinates), $this->entitiesByLocation)) {
            return $entities;
        }

        $notabilityWeights = [
            VarietyRepositoryConfig::HUMAN        => 1,
            VarietyRepositoryConfig::GARDEN_PLOT  => 25,
            VarietyRepositoryConfig::WELL         => 50,
            VarietyRepositoryConfig::WOODEN_CRATE => 100,
            VarietyRepositoryConfig::TOOLBOX      => 100,
        ];

        foreach ($this->entitiesByLocation[self::createKey($coordinates)] as $entity) {
            if (array_key_exists(strval($entity->getVarietyId()), $notabilityWeights)) {
                $entities[] = $entity;
            }
        }

        usort($entities, function (Entity $entityA, Entity $entityB) use ($notabilityWeights) {
            return $entityA->getOrderIndex() < $entityB->getOrderIndex()
                ? 1
                : -1;
        });

        usort($entities, function (Entity $entityA, Entity $entityB) use ($notabilityWeights) {
            return $notabilityWeights[strval($entityA->getVarietyId())] < $notabilityWeights[strval($entityB->getVarietyId())]
                ? 1
                : -1;
        });

        return $entities;
    }

    private function hasLocationById(UuidInterface $locationId): bool
    {
        foreach ($this->locations as $location) {
            if ($location->getId()->equals($locationId)) {
                return true;
            }
        }

        return false;
    }

    private function findLocationById(UuidInterface $locationId): Location
    {
        foreach ($this->locations as $location) {
            if ($location->getId()->equals($locationId)) {
                return $location;
            }
        }

        throw new DomainException;
    }
}
