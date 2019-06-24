<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use Aura\Session\Segment;

final class Alert
{
    public static function fromSession(Segment $session): ?self
    {
        $alertLevels = [
            "danger"  => "danger",
            "warning" => "warning",
            "success" => "success",
            "info"    => "info",
        ];

        foreach ($alertLevels as $alertLevel => $classSuffix) {
            if ($session->getFlash($alertLevel)) {
                return new self($session->getFlash($alertLevel), $classSuffix);
            }
        }

        return null;
    }

    private function __construct(string $message, string $classSuffix)
    {
        $this->message = $message;
        $this->classSuffix = $classSuffix;
    }
}
