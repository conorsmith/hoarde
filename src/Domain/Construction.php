<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class Construction
{
    public static function constructed(): self
    {
        return new self(true, 0, 0);
    }

    /** @var bool */
    private $isConstructed;

    /** @var int */
    private $remainingSteps;

    /** @var int */
    private $requiredSteps;

    public function __construct(bool $isConstructed, int $remainingSteps, int $requiredSteps)
    {
        $this->isConstructed = $isConstructed;
        $this->remainingSteps = $remainingSteps;
        $this->requiredSteps = $requiredSteps;
    }

    public function isConstructed(): bool
    {
        return $this->isConstructed;
    }

    public function getRemainingSteps(): int
    {
        return $this->remainingSteps;
    }

    public function getRequiredSteps(): int
    {
        return $this->requiredSteps;
    }

    public function takeAStep(): self
    {
        $newRemainingSteps = $this->remainingSteps - 1;

        return new self(
            $newRemainingSteps === 0,
            $newRemainingSteps,
            $this->requiredSteps
        );
    }
}
