<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerViewsLocation;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\Map;

final class GameState
{
    /** @var Game */
    private $game;

    /** @var Location */
    private $location;

    /** @var Entity */
    private $human;

    /** @var iterable */
    private $entities;

    /** @var iterable */
    private $actions;

    /** @var iterable */
    private $varietiesWithBlueprints;

    /** @var Map */
    private $map;

    public function __construct(
        Game $game,
        Location $location,
        Entity $human,
        iterable $entities,
        iterable $actions,
        iterable $varietiesWithBlueprints,
        Map $map
    ) {
        $this->game = $game;
        $this->location = $location;
        $this->human = $human;
        $this->entities = $entities;
        $this->actions = $actions;
        $this->varietiesWithBlueprints = $varietiesWithBlueprints;
        $this->map = $map;
    }

    public function getGame(): Game
    {
        return $this->game;
    }

    public function getLocation(): Location
    {
        return $this->location;
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

    public function getMap(): Map
    {
        return $this->map;
    }
}
