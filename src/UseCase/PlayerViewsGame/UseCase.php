<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerViewsGame;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use ConorSmith\Hoarde\Domain\ActionRepository;
use ConorSmith\Hoarde\Domain\Entity;
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

    /** @var ActionRepository */
    private $actionRepository;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        ActionRepository $actionRepository
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->actionRepository = $actionRepository;
    }

    public function __invoke(UuidInterface $gameId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $entities = $this->entityRepository->allInGame($gameId);

        if (is_null($game)) {
            return Result::failed(GeneralResult::gameNotFound($gameId));
        }

        $human = $this->findHuman($entities);

        if (is_null($human)) {
            return Result::failed(GeneralResult::failed("Game {$gameId} has no human entity"));
        }

        return Result::succeeded(new GameState(
            $game,
            $human,
            $entities,
            $this->actionRepository->all(),
            $this->varietyRepository->allWithBlueprints()
        ));
    }

    private function findHuman(iterable $entities): ?Entity
    {
        foreach ($entities as $entity) {
            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                return $entity;
            }
        }

        return null;
    }
}