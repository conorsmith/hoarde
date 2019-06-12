<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb;
use ConorSmith\Hoarde\Infra\Repository\GameRepositoryDb;
use Doctrine\DBAL\Connection;
use InvalidArgumentException;
use Throwable;

final class UnitOfWorkDb implements UnitOfWork
{
    /** @var Connection */
    private $db;

    /** @var array */
    private $repositories;

    /** @var array */
    private $dirtyObjects;

    public function __construct(
        Connection $db,
        GameRepositoryDb $gameRepositoryDb,
        EntityRepositoryDb $entityRepositoryDb
    ) {
        $this->db = $db;

        $this->repositories = [
            Game::class   => $gameRepositoryDb,
            Entity::class => $entityRepositoryDb,
        ];

        $this->dirtyObjects = [
            Game::class   => [],
            Entity::class => [],
        ];
    }

    public function registerDirty($object): void
    {
        if ($object instanceof Entity) {
            $this->dirtyObjects[Entity::class][] = $object;

        } elseif ($object instanceof Game) {
            $this->dirtyObjects[Game::class][] = $object;

        } else {
            throw new InvalidArgumentException;
        }
    }

    public function commit(): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($this->dirtyObjects as $class => $dirtyObjects) {
                foreach ($dirtyObjects as $dirtyObject) {
                    $this->repositories[$class]->save($dirtyObject);
                }
            }

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
