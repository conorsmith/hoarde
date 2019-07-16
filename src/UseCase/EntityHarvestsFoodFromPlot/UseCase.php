<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityHarvestsFoodFromPlot;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(
        UuidInterface $gameId,
        UuidInterface $actorId,
        UuidInterface $targetId,
        UuidInterface $inventoryEntityId,
        UuidInterface $varietyId,
        int $quantity
    ): Result {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $target = $this->entityRepository->findInGame($targetId, $gameId);
        $inventoryEntity = $this->entityRepository->findInGame($inventoryEntityId, $gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($target)) {
            return Result::entityNotFound($targetId, $gameId);
        }

        if (is_null($inventoryEntity)) {
            return Result::entityNotFound($inventoryEntityId, $gameId);
        }

        if (!$target->hasInventory()) {
            return Result::entityHasNoInventory($target);
        }

        if (!$inventoryEntity->hasInventory()) {
            return Result::entityHasNoInventory($inventoryEntity);
        }

        if ($varietyId->equals(Uuid::fromString(VarietyRepositoryConfig::RADISH_PLANT))) {
            $harvestedVariety = $this->varietyRepository->find(Uuid::fromString(VarietyRepositoryConfig::RADISH));
        } else {
            throw new DomainException;
        }

        $harvestCounter = 0;
        $harvestedEntities = [];

        foreach ($target->getInventory()->getEntities() as $targetInventoryEntity) {
            if ($targetInventoryEntity->getVarietyId()->equals($varietyId)
                && $targetInventoryEntity->getConstruction()->isConstructed()
                && $harvestCounter < $quantity
            ) {
                $target->getInventory()->discardEntity($targetInventoryEntity);
                $harvestedEntities[] = $targetInventoryEntity;
                $harvestCounter++;
            }
        }

        $inventoryEntity->getInventory()->addItem(
            $harvestedVariety->createItemWithQuantity($quantity)
        );

        $actor->wait();

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($game);

        foreach ($harvestedEntities as $harvestedEntity) {
            $unitOfWork->delete($harvestedEntity);
        }

        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
