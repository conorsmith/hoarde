<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class UpdateEntitySettings
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $entityIds = $this->gameRepo->findEntityIds($gameId);

        $entity = $this->entityRepo->find(Uuid::fromString($args['entityId']));

        if (!in_array($entity->getId(), $entityIds)) {
            $this->session->setFlash("danger", "Settings request must be for entities from this game");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        if (strlen($_POST['label']) === 0) {
            $this->session->setFlash("danger", "Entity label cannot be empty");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        $entity->relabel($_POST['label']);
        $this->entityRepo->save($entity);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
