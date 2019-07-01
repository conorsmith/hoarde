<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerReadsWrittenWork;

use ConorSmith\Hoarde\Domain\WrittenWork;

final class Result
{
    public static function fromWrittenWork(WrittenWork $writtenWork): self
    {
        return new self($writtenWork);
    }

    public static function notFound(): self
    {
        return new self(null);
    }

    /** @var ?WrittenWork */
    private $writtenWork;

    private function __construct(?WrittenWork $writtenWork)
    {
        $this->writtenWork = $writtenWork;
    }

    public function foundWrittenWork(): bool
    {
        return !is_null($this->writtenWork);
    }

    public function getWrittenWork(): WrittenWork
    {
        return $this->writtenWork;
    }
}
