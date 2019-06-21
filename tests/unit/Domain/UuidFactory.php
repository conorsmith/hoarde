<?php
declare(strict_types=1);

namespace ConorSmith\HoardeTest\Unit\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UuidFactory
{
    public static function create(): UuidInterface
    {
        return Uuid::fromString("00000000-0000-0000-0000-000000000000");
    }

    public static function generate(): UuidInterface
    {
        return Uuid::uuid4();
    }
}
