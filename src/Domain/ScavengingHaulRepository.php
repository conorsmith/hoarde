<?php

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface ScavengingHaulRepository
{
    public function find(UuidInterface $id): ?ScavengingHaul;
    public function save(ScavengingHaul $scavengingHaul): void;
    public function delete(ScavengingHaul $scavengingHaul): void;
}
