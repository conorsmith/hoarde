<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class HaveEntityWait
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

    public function __invoke(): ResponseInterface
    {
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $entity->wait();
        $this->entityRepo->save($entity);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "Entity has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
