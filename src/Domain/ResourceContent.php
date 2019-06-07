<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class ResourceContent
{
    /** @var Resource */
    private $resource;

    /** @var int */
    private $amount;

    public function __construct(Resource $resource, int $amount)
    {
        $this->resource = $resource;
        $this->amount = $amount;
    }

    public function getResource(): Resource
    {
        return $this->resource;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }
}
