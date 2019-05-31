<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response;

final class ShowLandingPage
{
    public function __invoke(): ResponseInterface
    {
        ob_start();

        include __DIR__ . "/../Templates/landing.php";

        $body = ob_get_contents();

        ob_end_clean();

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }
}
