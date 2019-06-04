<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class CompileCssOutput
{
    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $response = new Response;

        $response->getBody()->write(
            $this->renderJsFiles()
        );

        $response = $response->withHeader("Content-Type", "text/css");

        return $response;
    }

    private function renderJsFiles(): string
    {
        ob_start();

        include __DIR__ . "/../../Frontend/Css/main.css";

        $output = ob_get_contents();

        ob_end_clean();

        return $output;
    }
}
