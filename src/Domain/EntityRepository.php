<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface EntityRepository
{
    public function allInGame(UuidInterface $gameId): iterable;
    public function allInLocation(UuidInterface $locationId, UuidInterface $gameId): iterable;
    public function find(UuidInterface $id): ?Entity;
    public function findInGame(UuidInterface $id, UuidInterface $gameId): ?Entity;
    public function save(Entity $entity): void;
    public function delete(Entity $entity): void;
}
