<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class Location
{
    /** @var UuidInterface */
    private $id;

    /** @var UuidInterface */
    private $gameId;

    /** @var Coordinates */
    private $coordinates;

    /** @var int */
    private $scavengingLevel;

    public function __construct(
        UuidInterface $id,
        UuidInterface $gameId,
        Coordinates $coordinates,
        int $scavengingLevel
    ) {
        $this->id = $id;
        $this->gameId = $gameId;
        $this->coordinates = $coordinates;
        $this->scavengingLevel = $scavengingLevel;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getGameId(): UuidInterface
    {
        return $this->gameId;
    }

    public function getCoordinates(): Coordinates
    {
        return $this->coordinates;
    }

    public function getScavengingLevel(): int
    {
        return $this->scavengingLevel;
    }
}
