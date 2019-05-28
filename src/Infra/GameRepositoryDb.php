<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\UuidInterface;

final class GameRepositoryDb implements GameRepository
{
    /** @var Connection */
    private $db;

    public function __construct(Connection $db)
    {
        $this->db = $db;
    }

    public function find(UuidInterface $id): ?Game
    {
        $row = $this->db->fetchAssoc("SELECT * FROM games WHERE id = ?", [
            strval($id),
        ]);

        if ($row === false) {
            return null;
        }

        return new Game($id, intval($row['turn_index']));
    }
}
