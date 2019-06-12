<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\ActionRepository;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\ResourceNeed;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
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

    /** @var ActionRepository */
    private $actionRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ResourceRepository $resourceRepo,
        ActionRepository $actionRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->resourceRepo = $resourceRepo;
        $this->actionRepo = $actionRepo;
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
            $entities[] = $this->entityRepo->find($entityId);
        }

        $human = $this->findHuman($entities);

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
            'actions'         => $this->presentActions($this->actionRepo->all()),
            'constructions'   => [
                (object) [
                    'id'        => VarietyRepositoryConfig::WELL,
                    'label'     => "Well",
                    'icon'      => "tint",
                    'turns'     => 10,
                    'tools'     => [
                        (object) [
                            'id'    => VarietyRepositoryConfig::SHOVEL,
                            'label' => "Shovel",
                            'icon'  => "tools",
                        ],
                    ],
                    'materials' => [
                        (object) [
                            'id'       => VarietyRepositoryConfig::ROPE,
                            'label'    => "Rope",
                            'icon'     => "tools",
                            'quantity' => 1,
                        ],
                        (object) [
                            'id'       => VarietyRepositoryConfig::BUCKET,
                            'label'    => "Bucket",
                            'icon'     => "fill",
                            'quantity' => 1,
                        ],
                    ],
                ],
                (object) [
                    'id'        => VarietyRepositoryConfig::WOODEN_CRATE,
                    'label'     => "Wooden Crate",
                    'icon'      => "box",
                    'turns'     => 3,
                    'tools'     => [
                        (object) [
                            'id'    => VarietyRepositoryConfig::HAMMER,
                            'label' => "Claw Hammer",
                            'icon'  => "hammer",
                        ],
                        (object) [
                            'id'    => VarietyRepositoryConfig::HAND_SAW,
                            'label' => "Hand Saw",
                            'icon'  => "tools",
                        ],
                    ],
                    'materials' => [
                        (object) [
                            'id'       => VarietyRepositoryConfig::TIMBER,
                            'label'    => "Timber",
                            'icon'     => "tree",
                            'quantity' => 10,
                        ],
                        (object) [
                            'id'       => VarietyRepositoryConfig::NAIL,
                            'label'    => "Nail",
                            'icon'     => "toolbox",
                            'quantity' => 60,
                        ],
                    ],
                ],
            ]
        ]);

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }

    private function findHuman(iterable $entities): ?Entity
    {
        foreach ($entities as $entity) {
            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                return $entity;
            }
        }

        return null;
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

    private function presentEntity(Entity $entity, iterable $entities): stdClass
    {
        $resourceNeeds = [];

        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $resourceNeeds[] = $this->presentResourceNeed($entity, $resourceNeed);
        }

        $items = [];

        foreach ($entity->getInventoryItems() as $item) {
            $presentedItem = $this->presentItem($item);
            $presentedItem->actions = [];

            foreach ($item->getVariety()->getActions() as $action) {
                if ($action->canBePerformedBy($entity->getVarietyId())) {
                    switch (strval($action->getId())) {
                        case ActionRepositoryConfig::CONSUME:
                        case ActionRepositoryConfig::PLACE:
                            $jsClass = "js-use";
                            break;
                        case ActionRepositoryConfig::CONSTRUCT:
                        case ActionRepositoryConfig::DIG:
                            $jsClass = "js-construct";
                            break;
                        default:
                            $jsClass = "";
                    }

                    $presentedItem->actions[] = (object)[
                        'id'      => $action->getId(),
                        'label'   => $action->getLabel(),
                        'icon'    => $action->getIcon(),
                        'jsClass' => $jsClass,
                    ];
                }
            }

            $items[] = $presentedItem;
        }

        $presentation = (object) [
            'id'                         => $entity->getId(),
            'varietyId'                  => $entity->getVarietyId(),
            'label'                      => $entity->getLabel(),
            'icon'                       => $entity->getIcon(),
            'isHuman'                    => $entity->getVarietyId()->equals(
                Uuid::fromString(VarietyRepositoryConfig::HUMAN)
            ),
            'construction'               => $this->presentConstruction($entity, $entities),
            'resourceNeeds'              => $resourceNeeds,
        ];

        if ($entity->hasInventory()) {
            $presentation->inventory = (object) [
                'weight'                => $entity->getInventoryWeight(),
                'capacity'              => $entity->getInventoryCapacity(),
                'isAtCapacity'          => $entity->getInventoryWeight() === $entity->getInventoryCapacity(),
                'weightPercentage'      => $entity->getInventoryWeight() / $entity->getInventoryCapacity() * 100,
                'items'                 => $items,
            ];
        }

        if ($presentation->inventory) {

            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                $crate = $this->getFirstEntityOfVariety(
                    $entities,
                    Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE)
                );

                if (!is_null($crate)) {
                    $presentation->inventory->initialTransferEntityId = $crate->getId();
                }

            } elseif ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
                $presentation->inventory->initialTransferEntityId = $this->getFirstEntityOfVariety(
                    $entities,
                    Uuid::fromString(VarietyRepositoryConfig::HUMAN)
                )->getId();

            } else {
                $presentation->inventory->initialTransferEntityId = null;
            }
        }

        return $presentation;
    }

    private function presentConstruction(Entity $entity, iterable $entities): stdClass
    {
        if (!$entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
            $human = $this->findHuman($entities);
            $actor = (object) [
                'id'       => $human->getId(),
                'label'    => $human->getLabel(),
                'hasTools' => $human->hasToolsFor($entity),
            ];
        } else {
            $actor = null;
        }

        return (object) [
            'isConstructed'  => $entity->getConstruction()->isConstructed(),
            'remainingSteps' => $entity->getConstruction()->getRemainingSteps(),
            'requiredSteps'  => $entity->getConstruction()->getRequiredSteps(),
            'actor'          => $actor,
        ];
    }

    private function getFirstEntityOfVariety(iterable $entities, UuidInterface $varietyId): ?Entity
    {
        foreach ($entities as $entity) {
            if ($entity->getVarietyId()->equals($varietyId)) {
                return $entity;
            }
        }

        return null;
    }

    private function presentItem(Item $item): ?stdClass
    {
        return (object) [
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
            foreach ($entity->getInventoryItems() as $item) {
                if ($item->getVariety()->getId()->equals($lastConsumedVarietyId)) {
                    $lastConsumedItem = $this->presentItem($item);
                }
            }
        }

        foreach ($entity->getInventoryItems() as $item) {
            foreach ($item->getVariety()->getResources() as $itemResource) {
                if ($itemResource->getId()->equals($resource->getId())
                    && !$item->getVariety()->getId()->equals($lastConsumedVarietyId)
                ) {
                    $items[] = $this->presentItem($item);
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
                $presentation[] = $this->presentEntity($entity, $entities);
            }
        }

        return json_encode($presentation);
    }

    private function presentActions(iterable $actions): iterable
    {
        $presentation = [];

        foreach ($actions as $action) {
            $presentation[] = (object) [
                'id'    => $action->getId(),
                'label' => $action->getLabel(),
            ];
        }

        return $presentation;
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
