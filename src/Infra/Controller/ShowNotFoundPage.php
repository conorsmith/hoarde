<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

final class ShowNotFoundPage
{
    public function __invoke()
    {
        include __DIR__ . "/../../not-found.php";
    }
}
