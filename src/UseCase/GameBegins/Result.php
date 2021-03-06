<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameBegins;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use Ramsey\Uuid\UuidInterface;

final class Result
{
    public static function succeeded(UuidInterface $gameId, UuidInterface $beginningLocationId): self
    {
        return new self(GeneralResult::succeeded(), $gameId, $beginningLocationId);
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var UuidInterface */
    private $gameId;

    /** @var UuidInterface */
    private $beginningLocationId;

    private function __construct(
        GeneralResult $generalResult,
        UuidInterface $gameId,
        UuidInterface $beginningLocationId
    ) {
        $this->generalResult = $generalResult;
        $this->gameId = $gameId;
        $this->beginningLocationId = $beginningLocationId;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getGameId(): UuidInterface
    {
        return $this->gameId;
    }

    public function getBeginningLocationId(): UuidInterface
    {
        return $this->beginningLocationId;
    }
}
