<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface LocationRepository
{
    public function find(UuidInterface $id): ?Location;
    public function save(Location $location): void;
}
