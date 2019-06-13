<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityFetchesWater;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
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

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $wellId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $well = $this->entityRepository->findInGame($wellId, $gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($well)) {
            return Result::entityNotFound($wellId, $gameId);
        }

        if (!$actor->hasInventory()) {
            return Result::failed("{$actor->getLabel()} has no inventory.");
        }

        if (!$well->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            return Result::failed("{$well->getLabel()} is not a Well.");
        }

        if (!$well->getConstruction()->isConstructed()) {
            return Result::failed("{$well->getLabel()} is not constructed.");
        }

        $availableCapacity = $actor->getInventory()->getCapacity() - $actor->getInventory()->getWeight();

        $waterBottlesRetrieved = min(
            intval(floor($availableCapacity / 500)),
            20 // 10 litre bucket -> 500 ml water bottles
        );

        if ($waterBottlesRetrieved > 0) {

            $item = $this->varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity($waterBottlesRetrieved);

            $actor->getInventory()->addItem($item);

        }

        $actor->wait();
        $game->proceedToNextTurn();

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($game);
        $unitOfWork->save($actor);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$actor->isIntact()) {
            return Result::actorExpired($actor);
        }

        return Result::succeeded();
    }
}
