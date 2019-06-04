<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CompileJsOutput
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response;

        $response->getBody()->write(
            $this->renderJsFiles()
        );

        $response = $response->withHeader("Content-Type", "text/javascript");

        return $response;
    }

    private function renderJsFiles(): string
    {
        ob_start();

        $controllers = scandir(__DIR__ . "/../../Frontend/Js/Controller");

        foreach ($controllers as $controller) {
            if (!in_array($controller, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/Controller/{$controller}";
            }
        }

        $models = scandir(__DIR__ . "/../../Frontend/Js/Model");

        foreach ($models as $model) {
            if (!in_array($model, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/Model/{$model}";
            }
        }

        $views = scandir(__DIR__ . "/../../Frontend/Js/View");

        foreach ($views as $view) {
            if (!in_array($view, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/View/{$view}";
            }
        }

        include __DIR__ . "/../../Frontend/Js/classes.js";
        include __DIR__ . "/../../Frontend/Js/main.js";

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
