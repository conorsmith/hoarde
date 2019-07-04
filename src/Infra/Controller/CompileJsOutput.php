<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Closure;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\HtmlResponse;

final class CompileJsOutput
{
    private const MODULE_WHITELIST = [
        "construct",
        "harvest",
        "repair",
        "scavenge",
        "sort",
        "sow",
        "transfer",
    ];

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        if ($args['fileName'] === "main") {
            $responseBody = $this->renderMainJsFiles();

        } elseif ($args['fileName'] === "utility") {
            $responseBody = $this->renderUtilityJsFiles();

        } elseif (in_array($args['fileName'], self::MODULE_WHITELIST)) {
            $responseBody = $this->renderJsFiles(ucfirst($args['fileName']));

        } else {

            $responseBody = $this->render(function () {

                include __DIR__ . "/../Templates/not-found.php";

            });

            return new HtmlResponse($responseBody, 404);
        }

        $response = (new Response)->withHeader("Content-Type", "text/javascript");

        $response->getBody()->write($responseBody);

        return $response;
    }

    private function renderJsFiles(string $module): string
    {
        return $this->render(function () use ($module) {

            $modulePaths = [
                __DIR__ . "/../../Frontend/Js/{$module}/Controller",
                __DIR__ . "/../../Frontend/Js/{$module}/Model",
                __DIR__ . "/../../Frontend/Js/{$module}/View",
            ];

            foreach ($modulePaths as $modulePath) {
                $moduleFiles = scandir($modulePath);

                foreach ($moduleFiles as $moduleFile) {
                    if (!in_array($moduleFile, [".", ".."])) {
                        include "{$modulePath}/{$moduleFile}";
                    }
                }
            }

            include __DIR__ . "/../../Frontend/Js/{$module}/exports.js";

        });
    }

    private function renderMainJsFiles(): string
    {
        return $this->render(function () {

            include __DIR__ . "/../../Frontend/Js/main.js";

        });
    }

    private function renderUtilityJsFiles(): string
    {
        return $this->render(function () {

            $views = scandir(__DIR__ . "/../../Frontend/Js/Utility");

            foreach ($views as $view) {
                if (!in_array($view, [".", ".."])) {
                    include __DIR__ . "/../../Frontend/Js/Utility/{$view}";
                }
            }

        });
    }

    private function render(Closure $callback): string
    {
        ob_start();

        $callback();

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
