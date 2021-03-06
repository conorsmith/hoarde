<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\App\FindActorLocation;
use ConorSmith\Hoarde\UseCase\EntityTravelsToLocation\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\RedirectResponse;

final class EntityTravelsToLocation
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
        $locationId = Uuid::fromString($args['locationId']);

        $result = $this->useCase->__invoke($gameId, $actorId, $locationId);

        if (!$result->isSuccessful()) {
            $this->session->setFlash("danger", $result->getMessage());
            $locationId = $this->findActorLocation->__invoke($gameId, $actorId);
            return new RedirectResponse("/{$gameId}/{$locationId}");
        }

        return new RedirectResponse("/{$gameId}/{$result->getNewLocationId()}");
    }
}
