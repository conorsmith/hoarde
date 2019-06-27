<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Coordinates;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationRepository;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class LocationRepositoryDb implements LocationRepository
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find(UuidInterface $id): ?Location
    {
        $row = $this->db->fetchAssoc("SELECT * FROM locations WHERE id = :id", [
            'id' => $id,
        ]);

        if ($row === false) {
            return null;
        }

        return $this->reconstituteLocation($row);
    }

    public function findOrigin(UuidInterface $gameId): ?Location
    {
        $row = $this->db->fetchAssoc(
            "SELECT * FROM locations WHERE x_coordinate = 0 AND y_coordinate = 0 AND game_id = :game_id",
            [
                'game_id' => $gameId,
            ]
        );

        if ($row === false) {
            return null;
        }

        return $this->reconstituteLocation($row);
    }

    private function reconstituteLocation(array $row): Location
    {
        return new Location(
            Uuid::fromString($row['id']),
            new Coordinates(
                intval($row['x_coordinate']),
                intval($row['y_coordinate'])
            ),
            intval($row['scavenging_level'])
        );
    }

    public function save(Location $location): void
    {
        $row = $this->db->fetchAssoc("SELECT * FROM locations WHERE id = :id", [
            'id' => $location->getId(),
        ]);

        if ($row === false ) {
            $this->db->insert("locations", [
                'id'               => $location->getId(),
                'x_coordinate'     => $location->getCoordinates()->getX(),
                'y_coordinate'     => $location->getCoordinates()->getY(),
                'scavenging_level' => $location->getScavengingLevel(),
            ]);
        } else {
            $this->db->update("locations", [
                'scavenging_level' => $location->getScavengingLevel(),
            ], [
                'id' => $location->getId(),
            ]);
        }
    }
}
