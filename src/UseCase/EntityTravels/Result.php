<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityTravels;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use Ramsey\Uuid\UuidInterface;

final class Result
{
    public static function succeeded(UuidInterface $newLocationId): self
    {
        return new self(GeneralResult::succeeded(), $newLocationId);
    }

    public static function failed(GeneralResult $generalResult): self
    {
        return new self($generalResult, null);
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var ?UuidInterface */
    private $newLocationId;

    private function __construct(
        GeneralResult $generalResult,
        ?UuidInterface $newLocationId
    ) {
        $this->generalResult = $generalResult;
        $this->newLocationId = $newLocationId;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getMessage(): string
    {
        return $this->generalResult->getMessage();
    }

    public function getNewLocationId(): ?UuidInterface
    {
        return $this->newLocationId;
    }
}
