<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\Domain\EntityRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId, UuidInterface $resourceId): Result
    {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($entityId)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        $chosenItem = null;

        $lastConsumedVarietyId = $entity->getResourceNeeds()[strval($resourceId)]->getLastConsumedVarietyId();

        if (!is_null($lastConsumedVarietyId)) {
            foreach ($entity->getInventory()->getItems() as $item) {
                if ($item->getVariety()->getId()->equals($lastConsumedVarietyId)) {
                    $chosenItem = $item;
                }
            }
        }

        if (is_null($chosenItem)) {
            foreach ($entity->getInventory()->getItems() as $item) {
                foreach ($item->getVariety()->getResources() as $resource) {
                    if ($resource->getId()->equals($resourceId)) {
                        $chosenItem = $item;
                    }
                }
            }
        }

        if (is_null($chosenItem)) {
            return Result::failed("{$entity->getLabel()} has none of this resource to consume");
        }

        $entity->consumeItem($chosenItem->getVariety()->getId());
        $this->entityRepository->save($entity);

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }
}
