<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityDiscardsItem;

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

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $itemVarietyId,
        int $quantityDropped
    ): Result {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($entity)) {
            return Result::failed("Entity was not found in this game.");
        }

        if (!$entity->hasItemInInventory($itemVarietyId)) {
            return Result::failed("{$entity->getLabel()} does not have any of this item.");
        }

        $droppedItem = $entity->dropItem($itemVarietyId, $quantityDropped);

        $this->entityRepository->save($entity);

        return Result::succeeded(
            "{$entity->getLabel()} dropped {$droppedItem->getVariety()->getLabel()} ({$quantityDropped})"
        );
    }
}
