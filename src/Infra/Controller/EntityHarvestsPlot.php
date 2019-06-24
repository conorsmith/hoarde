<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\UseCase\EntityHarvestsPlot\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class EntityHarvestsPlot
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
        $actorId = Uuid::fromString($args['actorId']);
        $targetId = Uuid::fromString($args['targetId']);
        $inventoryEntityId = Uuid::fromString($request->getParsedBody()['inventoryEntityId']);
        $varietyId = Uuid::fromString($request->getParsedBody()['varietyId']);
        $quantity = intval($request->getParsedBody()['quantity']);

        $result = $this->useCase->__invoke($gameId, $actorId, $targetId, $inventoryEntityId, $varietyId, $quantity);

        if (!$result->isSuccessful()) {
            $this->session->setFlash("danger", $result->getMessage());
        }

        $response = new Response();
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
