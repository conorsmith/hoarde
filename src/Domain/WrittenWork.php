<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class WrittenWork
{
    /** @var string */
    private $title;

    /** @var string */
    private $body;

    public function __construct(string $title, string $body)
    {
        $this->title = $title;
        $this->body = $body;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}
