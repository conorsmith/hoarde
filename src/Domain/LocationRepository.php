<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface LocationRepository
{
    public function allInGame(UuidInterface $gameId): iterable;
    public function allWithCoordinates(iterable $setOfCoordinates, UuidInterface $gameId): iterable;
    public function findInGame(UuidInterface $id, UuidInterface $gameId): ?Location;
    public function findOrigin(UuidInterface $gameId): ?Location;
    public function findByCoordinates(Coordinates $coordinates, UuidInterface $gameId): ?Location;
    public function save(Location $location): void;
    public function delete(Location $location): void;
}
