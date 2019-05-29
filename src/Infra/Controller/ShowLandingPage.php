<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

final class ShowLandingPage
{
    public function __invoke()
    {
        include __DIR__ . "/../../generate.php";
    }
}
