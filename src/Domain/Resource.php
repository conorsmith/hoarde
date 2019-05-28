<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use Ramsey\Uuid\UuidInterface;

final class Resource
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    public function __construct(UuidInterface $id, string $label)
    {
        $this->id = $id;
        $this->label = $label;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }
}
