<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityContinuesConstructingEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
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

        $construction = $target->getConstruction();

        if ($construction->isConstructed()) {
            return Result::failed("Target entity has already been constructed");
        }

        $requiredTools = $this->findRequiredTools($target);
        $actorInventory = $actor->getInventory();

        $meetsRequirements = true;

        foreach ($requiredTools as $tool) {
            if (!$actorInventory->containsItem(Uuid::fromString($tool))) {
                $meetsRequirements = false;
            }
        }

        if (!$meetsRequirements) {
            return Result::failed("Construction requirements not met.");
        }

        $actor->construct($target);
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

    private function findRequiredTools(Entity $target): iterable
    {
        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            return [
                VarietyRepositoryConfig::SHOVEL,
            ];
        }

        if ($target->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            return [
                VarietyRepositoryConfig::HAMMER,
                VarietyRepositoryConfig::HAND_SAW,
            ];
        }

        throw new RuntimeException("Invalid construction");
    }
}
