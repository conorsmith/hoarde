<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface ItemRepository
{
    public function find(UuidInterface $id): ?Item;
}
