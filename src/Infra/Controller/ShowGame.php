<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\Resource;
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

        $human = null;
        $crate = null;

        foreach ($entityIds as $entityId) {
            $entity = $this->entityRepo->find($entityId);
            if ($entity->getVarietyId()->equals(Uuid::fromString("fde2146a-c29d-4262-b96f-ec7b696eccad"))) {
                $human = $entity;
            } elseif ($entity->getVarietyId()->equals(Uuid::fromString("59593b72-3845-491e-9721-4452a337019b"))) {
                $crate = $entity;
            }
        }

        $body = $this->renderGameTemplate($game, $human, [
            'entity' => $this->presentEntity($human),
            'crate'  => $this->presentEntity($crate),
        ]);

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }

    private function renderGameTemplate(Game $game, Entity $entity, array $variables): string
    {
        $gameId = $game->getId();

        $danger = $this->session->getFlash("danger");
        $warning = $this->session->getFlash("warning");
        $success = $this->session->getFlash("success");
        $info = $this->session->getflash("info");

        $turnIndex = $game->getTurnIndex();

        $resources = [];
        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $resource = $this->resourceRepo->find($resourceNeed->getResource()->getId());

            $items = [];

            $lastConsumedVarietyId = $resourceNeed->getLastConsumedVarietyId();
            $lastConsumedItem = null;

            if (!is_null($lastConsumedVarietyId)) {
                foreach ($entity->getInventory() as $item) {
                    if ($item->getVariety()->getId()->equals($lastConsumedVarietyId)) {
                        $lastConsumedItem = (object) $this->presentItem($item);
                    }
                }
            }

            foreach ($entity->getInventory() as $item) {
                foreach ($item->getVariety()->getResources() as $itemResource) {
                    if ($itemResource->getId()->equals($resource->getId())
                        && !$item->getVariety()->getId()->equals($lastConsumedVarietyId)
                    ) {
                        $items[] = (object) $this->presentItem($item);
                    }
                }
            }

            $resources[] = [
                'id'               => $resource->getId(),
                'label'            => $resource->getLabel(),
                'level'            => $resourceNeed->getCurrentLevel(),
                'segmentWidth'     => 100 / $resourceNeed->getMaximumLevel(),
                'noItems'          => is_null($lastConsumedItem) && count($items) === 0,
                'lastConsumedItem' => $lastConsumedItem,
                'items'            => $items,
            ];
        }

        $inventory = [];
        foreach ($entity->getInventory() as $item) {
            $inventory[] = $this->presentItem($item);
        }

        $isIntact = $entity->isIntact();

        $inventoryWeight = $entity->getInventoryWeight() / $entity->getInventoryCapacity() * 100;
        $entityOverencumbered = $entity->isOverencumbered();

        $variables = array_merge(
            compact([
                "gameId",
                "danger",
                "warning",
                "success",
                "info",
                "turnIndex",
                "resources",
                "inventory",
                "isIntact",
                "inventoryWeight",
                "entityOverencumbered",
            ]),
            $variables
        );

        return $this->renderTemplate("game.php", $variables);
    }

    private function renderTemplate(string $path, array $variables = []): string
    {
        extract($variables);

        ob_start();

        include __DIR__ . "/../Templates/{$path}";

        $body = ob_get_contents();

        ob_end_clean();

        return $body;
    }

    private function presentEntity(?Entity $entity) {
        if (is_null($entity)) {
            return null;
        }

        $items = [];

        foreach ($entity->getInventory() as $item) {
            $items[] = (object) $this->presentItem($item);
        }

        return (object)[
            'id'        => $entity->getId(),
            'label'     => $entity->getLabel(),
            'icon'      => $entity->getIcon(),
            'inventory' => (object)[
                'weight'       => $entity->getInventoryWeight(),
                'capacity'     => $entity->getInventoryCapacity(),
                'isAtCapacity' => $entity->getInventoryWeight() === $entity->getInventoryCapacity(),
                'items'        => $items,
            ],
            'isHuman'  => $entity->getVarietyId()->equals(Uuid::fromString("fde2146a-c29d-4262-b96f-ec7b696eccad")),
        ];
    }

    private function presentItem(Item $item): array
    {
        return [
            'id'            => $item->getVariety()->getId(),
            'varietyId'     => $item->getVariety()->getId(),
            'label'         => $item->getVariety()->getLabel(),
            'quantity'      => $item->getQuantity(),
            'weight'        => $item->getVariety()->getWeight(),
            'icon'          => $item->getVariety()->getIcon(),
            'resourceLabel' => implode(", ", array_map(function (Resource $resource) {
                return $resource->getLabel();
            }, $item->getVariety()->getResources())),
            'description'   => $item->getVariety()->getDescription(),
        ];
    }
}
