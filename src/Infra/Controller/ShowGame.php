<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class ShowGame
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ResourceRepository */
    private $resourceRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ResourceRepository $resourceRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->resourceRepo = $resourceRepo;
        $this->session = $session;
    }

    public function __invoke(): ResponseInterface
    {
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $body = $this->renderTemplate($game, $entity, [
            'entity' => (object)[
                'label'     => $entity->getLabel(),
                'icon'      => $entity->getIcon(),
                'inventory' => (object)[
                    'weight'   => $entity->getInventoryWeight(),
                    'capacity' => $entity->getInventoryCapacity(),
                ],
            ],
        ]);

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }

    private function renderTemplate(Game $game, Entity $entity, array $variables): string
    {
        $gameId = $game->getId();

        $danger = $this->session->getFlash("danger");
        $warning = $this->session->getFlash("warning");
        $success = $this->session->getFlash("success");
        $info = $this->session->getflash("info");

        $turnIndex = $game->getTurnIndex();

        $resources = [];
        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $resources[] = [
                'label'        => $this->resourceRepo->find($resourceNeed->getResource()->getId())->getLabel(),
                'level'        => $resourceNeed->getCurrentLevel(),
                'segmentWidth' => 100 / $resourceNeed->getMaximumLevel(),
            ];
        }

        $inventory = [];
        foreach ($entity->getInventory() as $item) {
            $inventory[] = $this->presentItem($item);
        }

        $isIntact = $entity->isIntact();

        $inventoryWeight = $entity->getInventoryWeight() / $entity->getInventoryCapacity() * 100;
        $entityOverencumbered = $entity->isOverencumbered();

        extract($variables);

        ob_start();

        include __DIR__ . "/../Templates/index.php";

        $body = ob_get_contents();

        ob_end_clean();

        return $body;
    }

    private function presentItem(Item $item): array
    {
        return [
            'id'       => $item->getVariety()->getId(),
            'label'    => $item->getVariety()->getLabel(),
            'quantity' => $item->getQuantity(),
            'weight'   => $item->getVariety()->getWeight(),
            'icon'     => $item->getVariety()->getIcon(),
        ];
    }
}
