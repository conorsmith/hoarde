<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class FetchWater
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        VarietyRepository $varietyRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->varietyRepo = $varietyRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $game = $this->gameRepo->find($gameId);

        $entity = $this->entityRepo->find(Uuid::fromString($_POST['entityId']));
        $well = $this->entityRepo->find(Uuid::fromString($_POST['wellId']));

        if (!$entity->getGameId()->equals($gameId)
            || !$well->getGameId()->equals($gameId)
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("FetchWater request must be for an entity from this game");
            return $response;
        }

        if (!$well->getConstruction()->isConstructed()) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("Well is not constructed!");
            return $response;
        }

        $availableCapacity = $entity->getInventory()->getCapacity() - $entity->getInventory()->getWeight();
        $waterBottlesRetrieved = min(
            intval(floor($availableCapacity / 500)),
            20 // 10 litre bucket -> 500 ml water bottles
        );

        if ($waterBottlesRetrieved > 0) {

            $item = $this->varietyRepo->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity($waterBottlesRetrieved);

            $entity->getInventory()->addItem($item);

        }

        $entity->wait();

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
