<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Action as DomainModel;

final class Action
{
    public static function createMany(iterable $actions): iterable
    {
        $presentations = [];

        foreach ($actions as $action) {
            $presentations[] = new self($action);
        }

        return $presentations;
    }

    public function __construct(DomainModel $action)
    {
        $this->id = strval($action->getId());
        $this->label = $action->getLabel();
    }
}
