<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityScavenges;

use ConorSmith\Hoarde\App\Result as GeneralResult;

final class Result
{
    public static function failedBecause(GeneralResult $result): self
    {
        return new self($result);
    }

    public static function succeeded(array $haul): self
    {
        return new self(GeneralResult::succeeded(), $haul);
    }

    public static function failed(string $message): self
    {
        return new self(GeneralResult::failed($message));
    }

    /** @var GeneralResult */
    private $generalResult;

    /** @var ?array */
    private $haul;

    private function __construct(GeneralResult $generalResult, array $haul = null)
    {
        $this->generalResult = $generalResult;
        $this->haul = $haul;
    }

    public function isSuccessful(): bool
    {
        return $this->generalResult->isSuccessful();
    }

    public function getMessage(): ?string
    {
        return $this->generalResult->getMessage();
    }

    public function getHaul(): ?array
    {
        return $this->haul;
    }
}
