<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;

final class Direction
{
    private const VALID_DIRECTIONS = [
        "north",
        "south",
        "east",
        "west",
        "north-east",
        "north-west",
        "south-east",
        "south-west",
    ];

    public static function isValid(string $value): bool
    {
        return in_array($value, self::VALID_DIRECTIONS);
    }

    /** @var string */
    private $value;

    public function __construct(string $value)
    {
        if (!self::isValid($value)) {
            throw new DomainException;
        }

        $this->value = $value;
    }

    public function isNortherly(): bool
    {
        return $this->value === "north"
            || $this->value === "north-east"
            || $this->value === "north-west";
    }

    public function isSoutherly(): bool
    {
        return $this->value === "south"
            || $this->value === "south-east"
            || $this->value === "south-west";
    }

    public function isEasterly(): bool
    {
        return $this->value === "east"
            || $this->value === "north-east"
            || $this->value === "south-east";
    }

    public function isWesterly(): bool
    {
        return $this->value === "west"
            || $this->value === "north-west"
            || $this->value === "south-west";
    }
}
