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

final class HaveEntityConstruct
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
        $game = $this->gameRepo->find($gameId);

        $actor = $this->entityRepo->find(Uuid::fromString($_POST['actorId']));
        $target = $this->entityRepo->find(Uuid::fromString($_POST['targetId']));

        if (!$actor->getGameId()->equals($gameId)
            || !$target->getGameId()->equals($gameId)
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("HaveEntityConstruct request must be for an entity from this game");
            return $response;
        }

        $construction = $target->getConstruction();

        if ($construction->isConstructed()) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("Target entity has already been constructed");
            return $response;
        }

        $actor->construct($target);

        $this->entityRepo->save($actor);
        $this->entityRepo->save($target);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$actor->isIntact()) {
            $this->session->setFlash("danger", "{$actor->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
