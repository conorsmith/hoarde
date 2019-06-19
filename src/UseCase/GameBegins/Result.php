<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameBegins;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use Ramsey\Uuid\UuidInterface;

final class Result
{
    public static function succeeded(UuidInterface $gameId): self
    {
        return new self(GeneralResult::succeeded(), $gameId);
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var UuidInterface */
    private $gameId;

    private function __construct(GeneralResult $generalResult, UuidInterface $gameId)
    {
        $this->generalResult = $generalResult;
        $this->gameId = $gameId;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getGameId(): UuidInterface
    {
        return $this->gameId;
    }
}
