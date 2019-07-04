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

        if ($direction->isNortherly()) {
            $yTranslation = 1;
        } elseif ($direction->isSoutherly()) {
            $yTranslation = -1;
        }

        if ($direction->isEasterly()) {
            $xTranslation = 1;
        } elseif ($direction->isWesterly()) {
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

    public function generateRouteTo(self $other): iterable
    {
        $translatedOther = new self(
            $other->x - $this->x,
            $other->y - $this->y
        );

        $longDistance = max(abs($translatedOther->x), abs($translatedOther->y));
        $shortDistance = min(abs($translatedOther->x), abs($translatedOther->y));

        $xDirection = $translatedOther->x < 0 ? -1 : 1;
        $yDirection = $translatedOther->y < 0 ? -1 : 1;

        $route = [];

        for ($i = 1, $j = 1; $i <= $longDistance; $i++, $j++) {
            if (abs($translatedOther->x) > abs($translatedOther->y)) {
                $route[] = new self(
                    $this->x + ($i * $xDirection),
                    $this->y + (min($j, $shortDistance) * $yDirection)
                );
            } else {
                $route[] = new self(
                    $this->x + (min($j, $shortDistance) * $xDirection),
                    $this->y + ($i * $yDirection)
                );
            }
        }

        return $route;
    }
}
