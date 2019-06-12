<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class HaveEntityUseItem
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
        $entityId = Uuid::fromString($args['entityId']);
        $itemId = Uuid::fromString($_POST['item']);

        $game = $this->gameRepo->find($gameId);
        $entity = $this->entityRepo->findInGame($entityId, $gameId);

        $consumedItem = $entity->consumeItem($itemId);
        $this->entityRepo->save($entity);

        if ($itemId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            $crate = new Entity(
                Uuid::uuid4(),
                $gameId,
                Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE),
                $consumedItem->getVariety()->getLabel(),
                $consumedItem->getVariety()->getIcon(),
                true,
                Construction::constructed(),
                [],
                []
            );

            $this->entityRepo->save($crate);

            $game->proceedToNextTurn();
            $this->gameRepo->save($game);
        }

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "{$entity->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
