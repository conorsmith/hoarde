<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class EntityConsumesResourceItem
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
        $entityId = Uuid::fromString($_POST['entityId']);
        $actorId = Uuid::fromString($_POST['actorId']);
        $resourceId = Uuid::fromString($_POST['resourceId']);

        $result = $this->useCase->__invoke($gameId, $entityId, $actorId, $resourceId);

        if (!$result->isSuccessful()) {
            $this->session->setFlash("danger", $result->getMessage());
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}