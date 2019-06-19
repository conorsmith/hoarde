<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

final class TemplateEngine
{
    public function render(string $template, array $variables = []): string
    {
        extract($variables);

        ob_start();

        include __DIR__ . "/Templates/{$template}";

        $body = ob_get_contents();

        ob_end_clean();

        return $body;
    }
}
