<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\UseCase\EntityScavenges\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class EntityScavenges
{
    /** @var Segment */
    private $session;

    /** @var UseCase */
    private $useCase;

    public function __construct(
        Segment $session,
        UseCase $useCase
    ) {
        $this->session = $session;
        $this->useCase = $useCase;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $entityId = Uuid::fromString($args['entityId']);

        $result = $this->useCase->__invoke($gameId, $entityId);

        if (!$result->isSuccessful()) {
            $response = new Response;
            $response = $response->withStatus(400);
            $response->getBody()->write(json_encode([
                'message' => $result->getMessage(),
            ]));
            return $response;
        }

        $response = new Response;
        $response->getBody()->write(json_encode([
            'haul' => $result->getHaul(),
        ]));
        return $response;
    }
}
