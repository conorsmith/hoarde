<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

interface UnitOfWork
{
    public function registerDirty($object): void;
    public function commit(): void;
}
