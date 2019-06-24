<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerViewsGame;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;

final class GameState
{
    /** @var Game */
    private $game;

    /** @var Entity */
    private $human;

    /** @var iterable */
    private $entities;

    /** @var iterable */
    private $actions;

    /** @var iterable */
    private $varietiesWithBlueprints;

    public function __construct(
        Game $game,
        Entity $human,
        iterable $entities,
        iterable $actions,
        iterable $varietiesWithBlueprints
    ) {
        $this->game = $game;
        $this->human = $human;
        $this->entities = $entities;
        $this->actions = $actions;
        $this->varietiesWithBlueprints = $varietiesWithBlueprints;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getHuman(): Entity
    {
        return $this->human;
    }

    public function getEntities(): iterable
    {
        return $this->entities;
    }

    public function getActions(): iterable
    {
        return $this->actions;
    }

    public function getVarietiesWithBlueprints(): iterable
    {
        return $this->varietiesWithBlueprints;
    }
}
