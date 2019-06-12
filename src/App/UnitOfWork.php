<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;
use InvalidArgumentException;

final class UnitOfWork
{
    /** @var array */
    private $objects;

    public function __construct()
    {
        $this->objects = [
            Game::class   => [],
            Entity::class => [],
        ];
    }

    public function save($object): void
    {
        if ($object instanceof Entity) {
            $this->objects[Entity::class][] = $object;

        } elseif ($object instanceof Game) {
            $this->objects[Game::class][] = $object;

        } else {
            throw new InvalidArgumentException;
        }
    }

    public function commit(UnitOfWorkProcessor $unitOfWorkProcessor): void
    {
        $unitOfWorkProcessor->commit($this->objects);
    }
}
