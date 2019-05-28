<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class Game
{
    /** @var UuidInterface */
    private $id;

    /** @var int */
    private $turnIndex;

    public function __construct(UuidInterface $id, int $turnIndex)
    {
        $this->id = $id;
        $this->turnIndex = $turnIndex;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTurnIndex(): int
    {
        return $this->turnIndex;
    }

    public function proceedToNextTurn(): void
    {
        $this->turnIndex++;
    }

    public function restart(): void
    {
        $this->turnIndex = 0;
    }
}
