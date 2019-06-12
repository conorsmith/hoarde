<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

interface UnitOfWorkProcessor
{
    public function commit(iterable $objects): void;
}
