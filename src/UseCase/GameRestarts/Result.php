<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameRestarts;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use Ramsey\Uuid\UuidInterface;

final class Result
{
    public static function succeeded(UuidInterface $beginningLocationId): self
    {
        return new self(GeneralResult::succeeded(), $beginningLocationId);
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var UuidInterface */
    private $beginningLocationId;

    private function __construct(
        GeneralResult $generalResult,
        UuidInterface $beginningLocationId
    ) {
        $this->generalResult = $generalResult;
        $this->beginningLocationId = $beginningLocationId;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getBeginningLocationId(): UuidInterface
    {
        return $this->beginningLocationId;
    }
}
