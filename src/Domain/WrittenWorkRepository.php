<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface WrittenWorkRepository
{
    public function find(UuidInterface $varietyId): ?WrittenWork;
}
