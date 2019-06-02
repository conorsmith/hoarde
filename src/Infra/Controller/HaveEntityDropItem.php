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

final class HaveEntityDropItem
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
        $entity = $this->entityRepo->find(Uuid::fromString($_POST['entityId']));

        if (!in_array($entity->getId(), $entityIds)) {
            $this->session->setFlash("danger", "Drop items request must be for entities from this game");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        $itemId = Uuid::fromString($_POST['item']);
        $droppedQuantity = intval($_POST['quantity']);
        $droppedItem = $entity->dropItem($itemId, $droppedQuantity);
        $this->entityRepo->save($entity);

        $this->session->setFlash("info", "{$entity->getLabel()} dropped {$droppedItem->getVariety()->getLabel()} ({$droppedQuantity})");

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
