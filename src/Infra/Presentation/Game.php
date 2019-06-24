<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Game as DomainModel;

final class Game
{
    public function __construct(DomainModel $game)
    {
        $this->id = strval($game->getId());
        $this->turnIndex = $game->getTurnIndex();
    }
}
