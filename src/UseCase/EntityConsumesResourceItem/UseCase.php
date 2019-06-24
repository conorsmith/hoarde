<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        EntityRepository $entityRepository,
        ResourceRepository $resourceRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->entityRepository = $entityRepository;
        $this->resourceRepository = $resourceRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $actorId,
        UuidInterface $resourceId
    ): Result {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $resource = $this->resourceRepository->find($resourceId);

        if (is_null($entityId)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (is_null($actorId)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($resource)) {
            return Result::failed("Resource {$resourceId} was not found.");
        }

        if (!$actor->hasInventory()) {
            return Result::entityHasNoInventory($actor);
        }

        $resourceNeed = $entity->findResourceNeed($resourceId);

        if (is_null($resourceNeed)) {
            return Result::failed("{$entity->getLabel()} has no need for {$resource->getLabel()}.");
        }

        if (!$actor->hasItemWithResourceContent($resourceId)) {
            return Result::failed("{$actor->getLabel()} has no {$resource->getLabel()}.");
        }

        $entity->consumeItem(
            $actor->takeItemForResourceNeed($resourceId)
        );

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($entity);
        $unitOfWork->save($actor);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$entity->isIntact()) {
            return Result::actorExpired($entity);
        }

        return Result::succeeded();
    }
}
