<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityDiscardsItem;

final class Result
{
    public static function succeeded(string $message): self
    {
        return new self(true, $message);
    }

    public static function failed(string $message): self
    {
        return new self(false, $message);
    }

    /** @var bool */
    private $isSuccessful;

    /** @var string */
    private $message;

    private function __construct(bool $isSuccessful, string $message)
    {
        $this->isSuccessful = $isSuccessful;
        $this->message = $message;
    }

    public function isSuccessful(): bool
    {
        return $this->isSuccessful;
    }

    public function getMessage(): string
    {
        return $this->message;
    }
}
