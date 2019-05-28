<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface GameRepository
{
    public function find(UuidInterface $id): ?Game;
}
