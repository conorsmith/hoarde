<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityWaits;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use Ramsey\Uuid\UuidInterface;

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

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (is_null($entity)) {
            return Result::failed("Entity {$entityId} was not found in game {$gameId}.");
        }

        $entity->wait();

        $unitOfWork = new UnitOfWork;

        $game->proceedToNextTurn($this->entityRepository, $unitOfWork);

        $unitOfWork->save($game);
        $unitOfWork->save($entity);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$entity->isIntact()) {
            return Result::failed("{$entity->getLabel()} has expired.");
        }

        return Result::succeeded();
    }
}
