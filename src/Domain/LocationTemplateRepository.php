<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

interface LocationTemplateRepository
{
    public function generateNewLocation(Coordinates $coordinates, UuidInterface $gameId): LocationTemplate;
}
