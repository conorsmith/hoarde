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

        if ($targetVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            $tools = [
                VarietyRepositoryConfig::SHOVEL,
            ];

            $materials = [
                VarietyRepositoryConfig::ROPE   => 1,
                VarietyRepositoryConfig::BUCKET => 1,
            ];

            $targetConstruction = new Construction(false, 9, 10);
        } elseif ($targetVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            $tools = [
                VarietyRepositoryConfig::HAMMER,
                VarietyRepositoryConfig::HAND_SAW,
            ];

            $materials = [
                VarietyRepositoryConfig::TIMBER => 10,
                VarietyRepositoryConfig::NAIL   => 60,
            ];

            $targetConstruction = new Construction(false, 2, 3);
        } else {
            throw new RuntimeException("Invalid construction");
        }

        $actorInventory = $actor->getInventory();

        $meetsRequirements = true;

        foreach ($tools as $tool) {
            if (!$actorInventory->containsItem(Uuid::fromString($tool))) {
                $meetsRequirements = false;
            }
        }

        foreach ($materials as $material => $quantity) {
            if (!$actorInventory->containsItemAmountingToAtLeast(Uuid::fromString($material), $quantity)) {
                $meetsRequirements = false;
            }
        }

        if (!$meetsRequirements) {
            return Result::failed("Construction requirements not met.");
        }

        foreach ($materials as $varietyId => $quantity) {
            $actorInventory->discardItem(Uuid::fromString($varietyId), $quantity);
        }

        $target = new Entity(
            Uuid::uuid4(),
            $game->getId(),
            $targetVariety->getId(),
            $targetVariety->getLabel(),
            $targetVariety->getIcon(),
            true,
            $targetConstruction,
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
