<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;

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

    public function translate(Direction $direction): self
    {
        $xTranslation = 0;
        $yTranslation = 0;

        if ($direction->isNorth()) {
            $yTranslation = 1;
        } elseif ($direction->isSouth()) {
            $yTranslation = -1;
        } elseif ($direction->isEast()) {
            $xTranslation = 1;
        } elseif ($direction->isWest()) {
            $xTranslation = -1;
        }

        return new self($this->x + $xTranslation, $this->y + $yTranslation);
    }

    public function allCoordinatesInSquare(int $length): iterable
    {
        if ($length % 2 === 0) {
            throw new DomainException;
        }

        $maximumOffset = intval(floor($length / 2));

        $coordinates = [];

        for ($y = $maximumOffset; $y >= 0 - $maximumOffset; $y--) {
            for ($x = 0 - $maximumOffset; $x <= $maximumOffset; $x++) {
                $coordinates[] = new self($this->x + $x, $this->y + $y);
            }
        }

        return $coordinates;
    }
}
