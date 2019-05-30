<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\ScavengingHaul;
use ConorSmith\Hoarde\Domain\ScavengingHaulRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ScavengingHaulRepositoryDb implements ScavengingHaulRepository
{
    /** @var Connection */
    private $db;

    /** @var VarietyRepository */
    private $varietyRepository;

    public function __construct(Connection $db, VarietyRepository $varietyRepository)
    {
        $this->db = $db;
        $this->varietyRepository = $varietyRepository;
    }

    public function find(UuidInterface $id): ?ScavengingHaul
    {
        $rows = $this->db->fetchAll("SELECT * FROM scavenging_haul_items WHERE haul_id = :haul_id", [
            'haul_id' => $id,
        ]);

        $items = [];

        foreach ($rows as $row) {
            $items[] = $this->varietyRepository
                ->find(Uuid::fromString($row['variety_id']))
                ->createItemWithQuantity(intval($row['quantity']));
        }

        return new ScavengingHaul($id, $items);
    }

    public function save(ScavengingHaul $scavengingHaul): void
    {
        foreach ($scavengingHaul->getItems() as $item) {
            $this->db->insert("scavenging_haul_items", [
                'haul_id'    => $scavengingHaul->getId(),
                'variety_id' => $item->getVariety()->getId(),
                'quantity'   => $item->getQuantity(),
            ]);
        }
    }

    public function delete(ScavengingHaul $scavengingHaul): void
    {
        $this->db->delete("scavenging_haul_items", [
            'haul_id' => $scavengingHaul->getId(),
        ]);
    }
}
