<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra;

use Symfony\Component\DomCrawler\Crawler;

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

    private function e(string $valueToEscape): string
    {
        return htmlspecialchars($valueToEscape, ENT_QUOTES | ENT_HTML5);
    }

    private function renderHtml5Template(string $template, array $variables = []): string
    {
        $templateContents = (new Crawler(
            $this->render($template, $variables)
        ))
            ->filter("template")
            ->children();

        $renderedHtml = "";

        foreach ($templateContents as $domElement) {
            $renderedHtml .= $domElement->ownerDocument->saveHTML($domElement);
        }

        return $renderedHtml;
    }
}
