<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityWaits;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var UnitOfWork */
    private $unitOfWork;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        UnitOfWork $unitOfWork
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->unitOfWork = $unitOfWork;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($game)) {
            return Result::failed("Game {$gameId} was not found.");
        }

        if (is_null($entity)) {
            return Result::failed("Entity {$entityId} was not found in game {$gameId}.");
        }

        $entity->wait();
        $game->proceedToNextTurn();

        $this->unitOfWork->registerDirty($game);
        $this->unitOfWork->registerDirty($entity);
        $this->unitOfWork->commit();

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }
}
