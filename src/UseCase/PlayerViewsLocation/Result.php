<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerViewsLocation;

use ConorSmith\Hoarde\App\Result as GeneralResult;

final class Result
{
    public static function succeeded(GameState $gameState): self
    {
        return new self(GeneralResult::succeeded(), $gameState);
    }

    public static function failed(GeneralResult $generalResult): self
    {
        return new self($generalResult, null);
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var ?GameState */
    private $gameState;

    private function __construct(GeneralResult $generalResult, ?GameState $gameState)
    {
        $this->generalResult = $generalResult;
        $this->gameState = $gameState;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getMessage(): string
    {
        return $this->generalResult->getMessage();
    }

    public function getGameState(): ?GameState
    {
        return $this->gameState;
    }
}
