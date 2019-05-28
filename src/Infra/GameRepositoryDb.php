<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use Doctrine\DBAL\Connection;
use Ramsey\Uuid\Uuid;
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

    public function save(Game $game): void
    {
        $row = $this->db->fetchAssoc("SELECT * FROM games WHERE id = :id", [
            'id' => $game->getId(),
        ]);

        if ($row === false ) {
            $this->db->insert("games", [
                'id' => $game->getId(),
                'turn_index' => $game->getTurnIndex(),
            ]);
        } else {
            $this->db->update("games", [
                'turn_index' => $game->getTurnIndex(),
            ], [
                'id' => $game->getId(),
            ]);
        }
    }

    public function findEntityIds(UuidInterface $id): iterable
    {
        $rows = $this->db->fetchAll("SELECT * FROM entities WHERE game_id = ?", [
            strval($id),
        ]);

        $ids = [];

        foreach ($rows as $row) {
            $ids[] = Uuid::fromString($row['id']);
        }

        return $ids;
    }
}
