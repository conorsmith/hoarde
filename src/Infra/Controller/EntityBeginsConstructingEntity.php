<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\App\FindActorLocation;
use ConorSmith\Hoarde\UseCase\EntityBeginsConstructingEntity\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class EntityBeginsConstructingEntity
{
    /** @var Segment */
    private $session;

    /** @var UseCase */
    private $useCase;

    /** @var FindActorLocation */
    private $findActorLocation;

    public function __construct(
        Segment $session,
        UseCase $useCase,
        FindActorLocation $findActorLocation
    ) {
        $this->session = $session;
        $this->useCase = $useCase;
        $this->findActorLocation = $findActorLocation;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $actorId = Uuid::fromString($args['actorId']);
        $constructionVarietyId = Uuid::fromString($_POST['constructionVarietyId']);

        $result = $this->useCase->__invoke($gameId, $actorId, $constructionVarietyId);

        if (!$result->isSuccessful()) {
            $this->session->setFlash("danger", $result->getMessage());
        }

        $locationId = $this->findActorLocation->__invoke($gameId, $actorId);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}/{$locationId}");
        return $response;
    }
}
