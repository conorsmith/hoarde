<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationRepository;
use Doctrine\DBAL\Connection;

final class LocationRepositoryDb implements LocationRepository
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
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
