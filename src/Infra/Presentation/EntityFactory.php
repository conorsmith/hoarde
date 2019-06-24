<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\Action as ActionDomainModel;
use ConorSmith\Hoarde\Domain\Entity as DomainModel;
use ConorSmith\Hoarde\Domain\Item;
use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\ResourceNeed;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use stdClass;

final class EntityFactory
{
    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(VarietyRepository $varietyRepository, ResourceRepository $resourceRepository)
    {
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function createEntity(DomainModel $entity, UuidInterface $actorId, iterable $entities): stdClass
    {
        $actor = $this->findActor($actorId, $entities);

        $resourceNeeds = [];

        foreach ($entity->getResourceNeeds() as $resourceNeed) {
            $resourceNeeds[] = $this->presentResourceNeed($actor, $resourceNeed);
        }

        $inventoryContents = [];

        if ($entity->hasInventory()) {
            foreach ($entity->getInventory()->getItems() as $item) {
                $presentedItem = $this->presentItem($item);
                $presentedItem->performableActions = [];

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

                        $presentedItem->performableActions[] = (object)[
                            'id'      => $action->getId(),
                            'actorId' => $actor->getId(),
                            'label'   => $action->getLabel(),
                            'icon'    => $action->getIcon(),
                            'jsClass' => $jsClass,
                        ];
                    }
                }

                $inventoryContents[] = $presentedItem;
            }

            foreach ($entity->getInventory()->getEntities() as $inventoryEntity) {
                $variety = $this->varietyRepository->find($inventoryEntity->getVarietyId());
                $presentedEntity = (object) [
                    'id'            => $inventoryEntity->getId(),
                    'varietyId'     => $variety->getId(),
                    'label'         => $inventoryEntity->getLabel(),
                    'quantity'      => 1,
                    'weight'        => $variety->getWeight(),
                    'icon'          => $inventoryEntity->getIcon(),
                    'resourceLabel' => implode(", ", array_map(function (Resource $resource) {
                        return $resource->getLabel();
                    }, $variety->getResources())),
                    'description'   => nl2br($variety->getDescription()),
                    'actions'       => array_values(array_map(function (ActionDomainModel $action) {
                        return (object) [
                            'id'    => $action->getId(),
                            'label' => $action->getLabel(),
                            'icon'  => $action->getIcon(),
                        ];
                    }, $variety->getActions())),
                ];

                foreach ($variety->getActions() as $action) {
                    if ($action->canBePerformedBy($entity->getVarietyId())) {
                        $presentedEntity->performableActions[] = (object)[
                            'id'      => $action->getId(),
                            'actorId' => $entity->getId(),
                            'label'   => $action->getLabel(),
                            'icon'    => $action->getIcon(),
                            'jsClass' => $this->findJsClassForAction($action),
                        ];
                    }
                }

                $inventoryContents[] = $presentedEntity;
            }
        }

        $presentation = (object) [
            'id'                         => $entity->getId(),
            'varietyId'                  => $entity->getVarietyId(),
            'label'                      => $entity->getLabel(),
            'icon'                       => $entity->getIcon(),
            'isIntact'                   => $entity->isIntact(),
            'isHuman'                    => $entity->getVarietyId()->equals(
                Uuid::fromString(VarietyRepositoryConfig::HUMAN)
            ),
            'construction'               => $this->presentConstruction($entity, $actor),
            'resourceNeeds'              => $resourceNeeds,
        ];

        if ($entity->hasInventory()) {
            $inventory = $entity->getInventory();
            $presentation->inventory = (object) [
                'weight'                => $inventory->getWeight(),
                'capacity'              => $inventory->getCapacity(),
                'isAtCapacity'          => $inventory->getWeight() === $inventory->getCapacity(),
                'weightPercentage'      => $inventory->getWeight() / $inventory->getCapacity() * 100,
                'items'                 => $inventoryContents,
            ];
        }

        if (isset($presentation->inventory)) {

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

        if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::GARDEN_PLOT))) {
            $presentation->incubator = [];
            $presentation->incubatorCapacityUsed = 0;

            foreach ($entity->getInventory()->getEntities() as $inventoryEntity) {
                $key = "{$inventoryEntity->getVarietyId()}-{$inventoryEntity->getConstruction()->getRemainingSteps()}";
                if (!array_key_exists($key, $presentation->incubator)) {

                    $variety = $this->varietyRepository->find($inventoryEntity->getVarietyId());

                    if ($variety->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::RADISH_PLANT))) {
                        $harvestedVariety = $this->varietyRepository->find(Uuid::fromString(VarietyRepositoryConfig::RADISH));
                    } else {
                        throw new DomainException;
                    }

                    $construction = $inventoryEntity->getConstruction();

                    $presentation->incubator[$key] = (object) [
                        'varietyId'              => $inventoryEntity->getVarietyId(),
                        'label'                  => $variety->getLabel(),
                        'icon'                   => $variety->getIcon(),
                        'description'            => $variety->getDescription(),
                        'construction'           => (object)[
                            'percentage'     => ($construction->getRequiredSteps() - $construction->getRemainingSteps())
                                / $construction->getRequiredSteps() * 100,
                            'remainingSteps' => $construction->getRemainingSteps(),
                            'requiredSteps'  => $construction->getRequiredSteps(),
                        ],
                        'performableActions'     => [],
                        'quantity'               => 1,
                        'harvestedVarietyWeight' => $harvestedVariety->getWeight(),
                    ];

                    foreach ($variety->getActions() as $action) {
                        $presentation->incubator[$key]->performableActions[] = (object) [
                            'id'         => $action->getId(),
                            'label'      => $action->getLabel(),
                            'icon'       => $action->getIcon(),
                            'jsClass'    => $this->findJsClassForAction($action),
                            'isDisabled' => !$construction->isConstructed(),
                        ];
                    }

                } else {
                    $presentation->incubator[$key]->quantity++;
                }
                $presentation->incubatorCapacityUsed++;
            }

            $presentation->incubator = array_values($presentation->incubator);

            usort($presentation->incubator, function ($entityA, $entityB) {
                if ($entityA->construction->percentage === $entityB->construction->percentage) {
                    return strcasecmp($entityA->label, $entityB->label);
                }

                return $entityA->construction->percentage > $entityB->construction->percentage ? -1 : 1;
            });
        }

        return $presentation;
    }

    private function presentResourceNeed(DomainModel $actor, ResourceNeed $resourceNeed): stdClass
    {
        $resource = $this->resourceRepository->find($resourceNeed->getResource()->getId());

        $items = [];

        $lastConsumedVarietyId = $resourceNeed->getLastConsumedVarietyId();
        $lastConsumedItem = null;

        if (!is_null($lastConsumedVarietyId)) {
            foreach ($actor->getInventory()->getItems() as $item) {
                if ($item->getVariety()->getId()->equals($lastConsumedVarietyId)) {
                    $lastConsumedItem = $this->presentItem($item);
                }
            }
        }

        foreach ($actor->getInventory()->getItems() as $item) {
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
            'actorId'          => $actor->getId(),
            'label'            => $resource->getLabel(),
            'level'            => $resourceNeed->getCurrentLevel(),
            'segmentWidth'     => 100 / $resourceNeed->getMaximumLevel(),
            'noItems'          => is_null($lastConsumedItem) && count($items) === 0,
            'lastConsumedItem' => $lastConsumedItem,
            'items'            => $items,
        ];
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
            'actions'       => array_values(array_map(function (ActionDomainModel $action) {
                return (object) [
                    'id'    => $action->getId(),
                    'label' => $action->getLabel(),
                    'icon'  => $action->getIcon(),
                ];
            }, $item->getVariety()->getActions())),
        ];
    }

    private function findJsClassForAction(ActionDomainModel $action): string
    {
        switch (strval($action->getId())) {
            case ActionRepositoryConfig::CONSUME:
            case ActionRepositoryConfig::PLACE:
                return "js-use";
            case ActionRepositoryConfig::CONSTRUCT:
            case ActionRepositoryConfig::DIG:
                return "js-construct";
            case ActionRepositoryConfig::HARVEST:
                return "js-harvest";
            default:
                return "";
        }
    }

    private function presentConstruction(DomainModel $entity, DomainModel $actor): stdClass
    {
        $actorPresentation = null;
        $entityVariety = $this->varietyRepository->find($entity->getVarietyId());

        if ($entityVariety->hasBlueprint()) {
            $actorPresentation = (object) [
                'id'       => $actor->getId(),
                'label'    => $actor->getLabel(),
                'hasTools' => $entityVariety->getBlueprint()->canContinueConstruction($actor->getInventory()),
            ];
        }

        return (object) [
            'isConstructed'  => $entity->getConstruction()->isConstructed(),
            'remainingSteps' => $entity->getConstruction()->getRemainingSteps(),
            'requiredSteps'  => $entity->getConstruction()->getRequiredSteps(),
            'actor'          => $actorPresentation,
        ];
    }

    private function getFirstEntityOfVariety(iterable $entities, UuidInterface $varietyId): ?DomainModel
    {
        foreach ($entities as $entity) {
            if ($entity->getVarietyId()->equals($varietyId)) {
                return $entity;
            }
        }

        return null;
    }

    private function findActor(UuidInterface $actorId, iterable $entities): DomainModel
    {
        foreach ($entities as $entity) {
            if ($entity->getId()->equals($actorId)) {
                return $entity;
            }
        }

        throw new DomainException;
    }
}
