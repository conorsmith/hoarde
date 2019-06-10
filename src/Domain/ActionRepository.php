<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface ActionRepository
{
    public function all(): iterable;
    public function find(UuidInterface $id): ?Action;
}
