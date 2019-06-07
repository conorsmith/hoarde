<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use DomainException;
use Ramsey\Uuid\UuidInterface;

final class Action
{
    /** @var UuidInterface */
    private $id;

    /** @var string */
    private $label;

    /** @var string */
    private $icon;

    /** @var array */
    private $perfomingVarietyIds;

    public function __construct(UuidInterface $id, string $label, string $icon, iterable $perfomingVarietyIds)
    {
        $this->id = $id;
        $this->label = $label;
        $this->icon = $icon;
        $this->perfomingVarietyIds = [];

        foreach ($perfomingVarietyIds as $perfomingVarietyId) {
            if (!$perfomingVarietyId instanceof UuidInterface) {
                throw new DomainException;
            }

            $this->perfomingVarietyIds[] = $perfomingVarietyId;
        }
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getPerformingVarietyIds(): iterable
    {
        return $this->perfomingVarietyIds;
    }

    public function canBePerformedBy(UuidInterface $varietyId) {
        return in_array($varietyId, $this->perfomingVarietyIds);
    }
}
