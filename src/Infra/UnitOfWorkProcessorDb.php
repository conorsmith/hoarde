<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\ScavengingHaul;
use ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb;
use ConorSmith\Hoarde\Infra\Repository\GameRepositoryDb;
use ConorSmith\Hoarde\Infra\Repository\ScavengingHaulRepositoryDb;
use Doctrine\DBAL\Connection;
use Throwable;

final class UnitOfWorkProcessorDb implements UnitOfWorkProcessor
{
    /** @var Connection */
    private $db;

    /** @var array */
    private $repositories;

    public function __construct(
        Connection $db,
        GameRepositoryDb $gameRepositoryDb,
        EntityRepositoryDb $entityRepositoryDb,
        ScavengingHaulRepositoryDb $scavengingHaulRepositoryDb
    ) {
        $this->db = $db;

        $this->repositories = [
            Game::class           => $gameRepositoryDb,
            Entity::class         => $entityRepositoryDb,
            ScavengingHaul::class => $scavengingHaulRepositoryDb,
        ];
    }

    public function commit(iterable $objects): void
    {
        $this->db->beginTransaction();

        try {
            foreach ($objects as $class => $classObjects) {
                foreach ($classObjects as $object) {
                    $this->repositories[$class]->save($object);
                }
            }

            $this->db->commit();

        } catch (Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
}
