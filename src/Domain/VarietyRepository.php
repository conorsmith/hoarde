<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface VarietyRepository
{
    public function find(UuidInterface $id): ?Variety;
}
