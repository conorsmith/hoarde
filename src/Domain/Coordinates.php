<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class Coordinates
{
    public static function origin(): self
    {
        return new self(0, 0);
    }

    /** @var int */
    private $x;

    /** @var int */
    private $y;

    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX(): int
    {
        return $this->x;
    }

    public function getY(): int
    {
        return $this->y;
    }
}
