<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Entity
{
    /** @var UuidInterface */
    private $id;

    /** @var iterable */
    private $resourceLevels;

    public function __construct(UuidInterface $id, iterable $resourceLevels)
    {
        $this->id = $id;
        $this->resourceLevels = $resourceLevels;

        foreach ($this->resourceLevels as $resourceLevel) {
            if (!$resourceLevel instanceof ResourceLevel) {
                throw new DomainException;
            }
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getResourceLevels(): iterable
    {
        return $this->resourceLevels;
    }
}
