<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class HaveEntityConsumeResource
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
        $entity = $this->entityRepo->find(Uuid::fromString($_POST['entityId']));

        if (!$entity->getGameId()->equals($gameId)) {
            $this->session->setFlash("danger", "Consume resource request must be for an entity from this game");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        $chosenItem = null;

        foreach ($entity->getInventory() as $item) {
            if ($item->getVariety()->getResource()->getId()->equals(Uuid::fromString($_POST['resourceId']))) {
                $chosenItem = $item;
            }
        }

        if (is_null($chosenItem)) {
            $this->session->setFlash(
                "danger",
                "{$entity->getLabel()} has none of this resource to consume"
            );

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        $usedItem = $entity->useItem($chosenItem->getVariety()->getId());
        $this->entityRepo->save($entity);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "{$entity->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
