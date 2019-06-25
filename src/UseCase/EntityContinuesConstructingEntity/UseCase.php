<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityContinuesConstructingEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $targetId): Result
    {
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

        if (!$actor->hasInventory()) {
            return Result::entityHasNoInventory($actor);
        }

        $targetVariety = $this->varietyRepository->find($target->getVarietyId());
        $construction = $target->getConstruction();

        if (!$targetVariety->hasBlueprint()) {
            return Result::failed("{$target->getLabel()} cannot be constructed.");
        }

        if ($construction->isConstructed()) {
            return Result::failed("{$target->getLabel()} has already been constructed");
        }

        if (!$targetVariety->getBlueprint()->canContinueConstruction($actor->getInventory())) {
            return Result::failed("Construction requirements not met.");
        }

        $actor->construct($target);

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

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
