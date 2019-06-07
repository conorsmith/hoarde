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
use ConorSmith\Hoarde\Domain\ResourceNeed;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use stdClass;
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
        $entities = [];

        foreach ($entityIds as $entityId) {
            $entity = $this->entityRepo->find($entityId);

            $entities[] = $entity;

            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                $human = $entity;
            }
        }

        if (is_null($human)) {
            throw new RuntimeException("Game is missing human entity");
        }

        $body = $this->renderTemplate("game.php", [
            'human'           => $this->presentEntity($human, $entities),
            'isIntact'        => $human->isIntact(),
            'alert'           => $this->presentAlert($this->session),
            'game'            => $this->presentGame($game),
            'entities'        => array_map(function (Entity $entity) use ($entities) {
                return $this->presentEntity($entity, $entities);
            }, $entities),
            'encodedEntities' => $this->jsonEncodeEntities($entities),
        ]);

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }

    private function presentAlert(Segment $session): ?stdClass
    {
        $alertLevels = [
            "danger"  => "danger",
            "warning" => "warning",
            "success" => "success",
            "info"    => "info",
        ];

        foreach ($alertLevels as $alertLevel => $classSuffix) {
            if ($session->getFlash($alertLevel)) {
                return (object) [
                    'message'     => $session->getFlash($alertLevel),
                    'classSuffix' => $classSuffix,
                ];
            }
        }

        return null;
    }

    private function presentGame(Game $game): stdClass
    {
        return (object) [
            'id'        => $game->getId(),
            'turnIndex' => $game->getTurnIndex(),
        ];
    }

    private function presentEntity(?Entity $entity, iterable $entities): ?stdClass
    {
        if (is_null($entity)) {
            return null;
        }

        $presentation = $this->transformEntityForPresentation($entity);

        if ($presentation->inventory) {

            $presentation->inventory->defaultTransferEntity = null;

            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                foreach ($entities as $otherEntity) {
                    if ($otherEntity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
                        $presentation->inventory->defaultTransferEntity
                            = $this->transformEntityForPresentation($otherEntity);
                    }
                }
            } elseif ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
                foreach ($entities as $otherEntity) {
                    if ($otherEntity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                        $presentation->inventory->defaultTransferEntity
                            = $this->transformEntityForPresentation($otherEntity);
                    }
                }
            }
        }

        return $presentation;
    }

    private function transformEntityForPresentation(Entity $entity): stdClass
    {
        $resourceNeeds = [];

        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $resourceNeeds[] = $this->presentResourceNeed($entity, $resourceNeed);
        }

        $items = [];

        foreach ($entity->getInventory() as $item) {
            $items[] = (object) $this->presentItem($item);
        }

        $presentation = (object) [
            'id'                         => $entity->getId(),
            'varietyId'                  => $entity->getVarietyId(),
            'label'                      => $entity->getLabel(),
            'icon'                       => $entity->getIcon(),
            'isHuman'                    => $entity->getVarietyId()->equals(
                Uuid::fromString(VarietyRepositoryConfig::HUMAN)
            ),
            'isConstructed'              => $entity->getConstruction()->isConstructed(),
            'remainingConstructionSteps' => $entity->getConstruction()->getRemainingSteps(),
            'requiredConstructionSteps'  => $entity->getConstruction()->getRequiredSteps(),
            'resourceNeeds'              => $resourceNeeds,
        ];

        if (!$entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            $presentation->inventory = (object) [
                'weight'                => $entity->getInventoryWeight(),
                'capacity'              => $entity->getInventoryCapacity(),
                'isAtCapacity'          => $entity->getInventoryWeight() === $entity->getInventoryCapacity(),
                'weightPercentage'      => $entity->getInventoryWeight() / $entity->getInventoryCapacity() * 100,
                'items'                 => $items,
            ];
        }

        return $presentation;
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
            'description'   => nl2br($item->getVariety()->getDescription()),
        ];
    }

    private function presentResourceNeed(Entity $entity, ResourceNeed $resourceNeed): stdClass
    {
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

        return (object) [
            'id'               => $resource->getId(),
            'label'            => $resource->getLabel(),
            'level'            => $resourceNeed->getCurrentLevel(),
            'segmentWidth'     => 100 / $resourceNeed->getMaximumLevel(),
            'noItems'          => is_null($lastConsumedItem) && count($items) === 0,
            'lastConsumedItem' => $lastConsumedItem,
            'items'            => $items,
        ];
    }

    private function jsonEncodeEntities(iterable $entities): string
    {
        $presentation = [];

        foreach ($entities as $entity) {
            if (!is_null($entity)) {
                $presentation[] = $this->transformEntityForPresentation($entity);
            }
        }

        return json_encode($presentation);
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
}
