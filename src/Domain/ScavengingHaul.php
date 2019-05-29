<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

final class ScavengingHaul
{
    /** @var ?Item */
    private $item;

    /** @var bool */
    private $isRetrievable;

    public function __construct(?Item $item, bool $isRetrievable)
    {
        $this->item = $item;
        $this->isRetrievable = $isRetrievable;
    }

    public function getItem(): ?Item
    {
        return $this->item;
    }

    public function hasItem(): bool
    {
        return !is_null($this->item);
    }

    public function isRetrievable(): bool
    {
        return $this->isRetrievable;
    }
}
