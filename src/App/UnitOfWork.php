<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\ScavengingHaul;
use InvalidArgumentException;

final class UnitOfWork
{
    /** @var array */
    private $savedObjects;

    /** @var array */
    private $deletedObjects;

    public function __construct()
    {
        $this->savedObjects = [
            Game::class           => [],
            Entity::class         => [],
            ScavengingHaul::class => [],
        ];

        $this->deletedObjects = [
            Game::class           => [],
            Entity::class         => [],
            ScavengingHaul::class => [],
        ];
    }

    public function save($object): void
    {
        if (!array_key_exists(get_class($object), $this->savedObjects)) {
            throw new InvalidArgumentException;
        }

        $this->savedObjects[get_class($object)][] = $object;
    }

    public function delete($object): void
    {
        if (!array_key_exists(get_class($object), $this->deletedObjects)) {
            throw new InvalidArgumentException;
        }

        $this->deletedObjects[get_class($object)][] = $object;
    }

    public function commit(UnitOfWorkProcessor $unitOfWorkProcessor): void
    {
        $unitOfWorkProcessor->commit($this->savedObjects, $this->deletedObjects);
    }
}
