<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

interface LocationRepository
{
    public function save(Location $location): void;
}
