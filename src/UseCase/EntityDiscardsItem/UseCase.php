<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityDiscardsItem;

use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(GameRepository $gameRepository, EntityRepository $entityRepository)
    {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $itemVarietyId,
        int $quantityDropped
    ): Result {
        $entityIds = $this->gameRepository->findEntityIds($gameId);
        $entity = $this->entityRepository->find($entityId);

        if (!in_array($entity->getId(), $entityIds)) {
            return Result::failed("Drop items request must be for entities from this game");
        }

        $droppedItem = $entity->dropItem($itemVarietyId, $quantityDropped);

        $this->entityRepository->save($entity);

        return Result::succeeded(
            "{$entity->getLabel()} dropped {$droppedItem->getVariety()->getLabel()} ({$quantityDropped})"
        );
    }
}
