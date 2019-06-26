<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CompileJsOutput
{
    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $response = new Response;

        if ($args['fileName'] === "main") {
            $output = $this->renderMainJsFiles();

        } elseif ($args['fileName'] === "transfer") {
            $output = $this->renderJsFiles("Transfer");

        } elseif ($args['fileName'] === "construct") {
            $output = $this->renderJsFiles("Construct");

        } elseif ($args['fileName'] === "sow") {
            $output = $this->renderJsFiles("Sow");

        } elseif ($args['fileName'] === "harvest") {
            $output = $this->renderJsFiles("Harvest");

        } elseif ($args['fileName'] === "repair") {
            $output = $this->renderJsFiles("Repair");

        } elseif ($args['fileName'] === "sort") {
            $output = $this->renderJsFiles("Sort");

        } else {

            ob_start();

            include __DIR__ . "/../Templates/not-found.php";

            $body = ob_get_contents();

            ob_end_clean();

            $response = $response->withStatus(404);
            $response->getBody()->write($body);

            return $response;
        }

        $response->getBody()->write($output);

        $response = $response->withHeader("Content-Type", "text/javascript");

        return $response;
    }

    private function renderJsFiles(string $namespace): string
    {
        ob_start();

        $controllers = scandir(__DIR__ . "/../../Frontend/Js/{$namespace}/Controller");

        foreach ($controllers as $controller) {
            if (!in_array($controller, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/{$namespace}/Controller/{$controller}";
            }
        }

        $models = scandir(__DIR__ . "/../../Frontend/Js/{$namespace}/Model");

        foreach ($models as $model) {
            if (!in_array($model, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/{$namespace}/Model/{$model}";
            }
        }

        $views = scandir(__DIR__ . "/../../Frontend/Js/{$namespace}/View");

        foreach ($views as $view) {
            if (!in_array($view, [".", ".."])) {
                include __DIR__ . "/../../Frontend/Js/{$namespace}/View/{$view}";
            }
        }

        include __DIR__ . "/../../Frontend/Js/{$namespace}/exports.js";

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }

    private function renderMainJsFiles(): string
    {
        ob_start();

        include __DIR__ . "/../../Frontend/Js/classes.js";
        include __DIR__ . "/../../Frontend/Js/main.js";

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
