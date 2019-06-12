<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    public function __construct(EntityRepository $entityRepository, ResourceRepository $resourceRepository)
    {
        $this->entityRepository = $entityRepository;
        $this->resourceRepository = $resourceRepository;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId, UuidInterface $resourceId): Result
    {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $resource = $this->resourceRepository->find($resourceId);

        if (is_null($entityId)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (is_null($resource)) {
            return Result::failed("Resource {$resourceId} was not found.");
        }

        if (!$entity->hasInventory()) {
            return Result::failed("{$entity->getLabel()} has no inventory.");
        }

        $resourceNeed = $entity->findResourceNeed($resourceId);

        if (is_null($resourceNeed)) {
            return Result::failed("{$entity->getLabel()} has no need for {$resource->getLabel()}.");
        }

        if (!$entity->hasItemWithResourceContent($resourceId)) {
            return Result::failed("{$entity->getLabel()} has no {$resource->getLabel()}.");
        }

        $entity->consumeItemForResourceNeed($resourceId);

        $this->entityRepository->save($entity);

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }
}
