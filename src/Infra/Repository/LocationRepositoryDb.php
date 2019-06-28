<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Coordinates;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationRepository;
use Doctrine\DBAL\Connection;
use PDO;
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

    public function allWithCoordinates(iterable $setOfCoordinates, UuidInterface $gameId): iterable
    {
        $xCoordinates = [];
        $yCoordinates = [];

        foreach ($setOfCoordinates as $coordinates) {
            $xCoordinates[] = $coordinates->getX();
            $yCoordinates[] = $coordinates->getY();
        }

        dd($xCoordinates, $yCoordinates, $this->db->fetchAssoc("SELECT * FROM locations WHERE game_id = :game_id", [
            'game_id' => $gameId,
        ]));

        $rows = $this->db->executeQuery(
            "
                SELECT * FROM locations
                  WHERE game_id = :game_id
                    AND x_coordinate IN (:x_coordinates)
                    AND y_coordinate IN (:y_coordinates)
            ",
            [
                'game_id'       => $gameId,
                'x_coordinates' => $xCoordinates,
                'y_coordinates' => $yCoordinates,
            ],
            [
                'game_id'       => PDO::PARAM_INT,
                'x_coordinates' => Connection::PARAM_INT_ARRAY,
                'y_coordinates' => Connection::PARAM_INT_ARRAY,
            ]
        )
            ->fetchAll();

        $locations = [];

        foreach ($rows as $row) {
            $locations[] = $this->reconstituteLocation($row);
        }

        return $locations;
    }

    public function findInGame(UuidInterface $id, UuidInterface $gameId): ?Location
    {
        $row = $this->db->fetchAssoc("SELECT * FROM locations WHERE id = :id AND game_id = :game_id", [
            'id'      => $id,
            'game_id' => $gameId,
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

    public function findByCoordinates(Coordinates $coordinates, UuidInterface $gameId): ?Location
    {
        $row = $this->db->fetchAssoc(
            "SELECT * FROM locations WHERE x_coordinate = :x AND y_coordinate = :y AND game_id = :game_id",
            [
                'x'       => $coordinates->getX(),
                'y'       => $coordinates->getY(),
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
            Uuid::fromString($row['game_id']),
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
                'game_id'          => $location->getGameId(),
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
