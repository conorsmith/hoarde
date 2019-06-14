<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntitySowsPlot;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
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
        iterable $plotContents
    ): Result {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $target = $this->entityRepository->findInGame($targetId, $gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($target)) {
            return Result::entityNotFound($targetId, $gameId);
        }

        $inventories = [];
        $otherEntities = [];

        if ($actor->hasInventory()) {
            $inventories[] = $actor->getInventory();
        }

        $entityIds = $this->gameRepository->findEntityIds($gameId);
        foreach ($entityIds as $entityId) {
            if (!$entityId->equals($actorId)
                && !$entityId->equals($targetId)
            ) {
                $entity = $this->entityRepository->find($entityId);
                if ($entity->hasInventory()) {
                    $otherEntities[] = $entity;
                    $inventories[] = $entity->getInventory();
                }
            }
        }

        $counters = [];

        foreach ($plotContents as $plotItem) {
            $counters[$plotItem['varietyId']] = $plotItem['quantity'];
        }

        foreach ($plotContents as $plotItem) {
            foreach ($inventories as $inventory) {
                $varietyId = Uuid::fromString($plotItem['varietyId']);

                if ($counters[strval($varietyId)] > 0
                    && $inventory->containsItem($varietyId)
                ) {
                    $quantityAvailable = $inventory->getItem($varietyId)->getQuantity();

                    if ($quantityAvailable >= $counters[strval($varietyId)]) {
                        $inventory->discardItem($varietyId, $counters[strval($varietyId)]);
                        $counters[strval($varietyId)] = 0;
                    } else {
                        $inventory->discardItem($varietyId, $quantityAvailable);
                        $counters[strval($varietyId)] -= $quantityAvailable;
                    }
                }
            }
        }

        $plotEntities = [];

        foreach ($plotContents as $plotItem) {
            $seedVarietyId = Uuid::fromString($plotItem['varietyId']);

            if ($seedVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::RADISH_SEED))) {
                $plantVariety = $this->varietyRepository->find(Uuid::fromString(VarietyRepositoryConfig::RADISH));
            } else {
                throw new DomainException;
            }

            for ($i = 0; $i < $plotItem['quantity']; $i++) {
                $plotEntities[] = new Entity(
                    Uuid::uuid4(),
                    $game->getId(),
                    $plantVariety->getId(),
                    $plantVariety->getLabel(),
                    $plantVariety->getIcon(),
                    true,
                    new Construction(
                        false,
                        83,
                        84
                    ),
                    [],
                    null
                );
            }
        }

        dd($plotEntities);

        foreach ($plotEntities as $entity) {
            $target->getInventory()->addEntity($entity);
        }

        $game->proceedToNextTurn();

        $unitOfWork = new UnitOfWork();
        $unitOfWork->save($game);
        $unitOfWork->save($actor);
        $unitOfWork->save($target);
        foreach ($otherEntities as $entity) {
            $unitOfWork->save($entity);
        }
        foreach ($plotEntities as $entity) {
            $unitOfWork->save($entity);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
