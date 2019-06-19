<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\UseCase\GameBegins\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response;

final class GenerateNewGame
{
    /** @var UseCase */
    private $useCase;

    public function __construct(UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $result = $this->useCase->__invoke(
            $request->getParsedBody()['label'],
            $request->getParsedBody()['icon']
        );

        $response = new Response;
        $response = $response->withHeader("Location", "/{$result->getGameId()}");
        return $response;
    }
}
