<?php
declare(strict_types=1);

namespace ConorSmith\HoardeTest\Unit\Domain;

use ConorSmith\Hoarde\Domain\Action;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class ActionBuilder
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $icon;

    /** @var iterable */
    private $performingVarietyIds;

    public function __construct()
    {
        $this->id = Uuid::uuid4();
        $this->label = "some label";
        $this->icon = "some icon";
        $this->performingVarietyIds = [];
    }

    public function withId(UuidInterface $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function withLabel(string $label): self
    {
        $this->label = $label;
        return $this;
    }

    public function withIcon(string $icon): self
    {
        $this->icon = $icon;
        return $this;
    }

    public function withPerformingVarietyIds(iterable $performingVarietyIds): self
    {
        $this->performingVarietyIds = $performingVarietyIds;
        return $this;
    }

    public function build(): Action
    {
        return new Action(
            $this->id,
            $this->label,
            $this->icon,
            $this->performingVarietyIds
        );
    }
}
