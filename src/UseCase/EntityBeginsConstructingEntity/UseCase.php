<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityBeginsConstructingEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $targetVarietyId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $targetVariety = $this->varietyRepository->find($targetVarietyId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($actor)) {
            return Result::entityNotFound($actorId, $gameId);
        }

        if (is_null($targetVariety)) {
            return Result::varietyNotFound($targetVarietyId);
        }

        if (!$targetVariety->hasBlueprint()) {
            return Result::failed("{$targetVariety->getLabel()} cannot be constructed.");
        }

        $blueprint = $targetVariety->getBlueprint();
        $actorInventory = $actor->getInventory();

        if (!$blueprint->canBeginConstruction($actorInventory)) {
            return Result::failed("Construction requirements not met.");
        }

        foreach ($blueprint->getMaterials() as $varietyId => $quantity) {
            $actorInventory->discardItem(Uuid::fromString($varietyId), $quantity);
        }

        $target = new Entity(
            Uuid::uuid4(),
            $game->getId(),
            $targetVariety->getId(),
            $targetVariety->getLabel(),
            $targetVariety->getIcon(),
            true,
            new Construction(false, $blueprint->getTurns() - 1, $blueprint->getTurns()),
            [],
            []
        );

        $actor->wait();
        $game->proceedToNextTurn();

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($game);
        $unitOfWork->save($actor);
        $unitOfWork->save($target);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$actor->isIntact()) {
            return Result::actorExpired($actor);
        }

        return Result::succeeded();
    }
}
