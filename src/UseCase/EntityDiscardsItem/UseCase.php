<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityDiscardsItem;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    public function __construct(EntityRepository $entityRepository, VarietyRepository $varietyRepository)
    {
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $entityId,
        UuidInterface $itemVarietyId,
        int $quantityDiscarded
    ): Result {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);
        $variety = $this->varietyRepository->find($itemVarietyId);

        if (is_null($entity)) {
            return Result::failed("Entity {$entityId} was not found in game {$gameId}.");
        }

        if (is_null($variety)) {
            return Result::failed("Variety {$itemVarietyId} was not found.");
        }

        if (!$entity->hasInventory()) {
            return Result::failed("Entity {$entityId} has no inventory.");
        }

        $inventory = $entity->getInventory();

        if (!$inventory->containsItemAmountingToAtLeast($itemVarietyId, $quantityDiscarded)) {
            return Result::failed("{$entity->getLabel()} does not have {$variety->getLabel()} ({$quantityDiscarded}).");
        }

        $inventory->discardItem($itemVarietyId, $quantityDiscarded);

        $this->entityRepository->save($entity);

        return Result::succeeded(
            "{$entity->getLabel()} discarded {$variety->getLabel()} ({$quantityDiscarded})."
        );
    }
}
