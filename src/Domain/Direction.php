<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;

final class Direction
{
    private const VALID_DIRECTIONS = ["north", "south", "east", "west"];

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

    public function isNorth(): bool
    {
        return $this->value === "north";
    }

    public function isSouth(): bool
    {
        return $this->value === "south";
    }

    public function isEast(): bool
    {
        return $this->value === "east";
    }

    public function isWest(): bool
    {
        return $this->value === "west";
    }
}
