<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameRestarts;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
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

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        ResourceRepository $resourceRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $oldEntities = $this->entityRepository->allInGame($gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (count($oldEntities) === 0) {
            return Result::failed("Game {$game->getId()} has no entities");
        }

        $oldBeginningEntity = $game->findBeginningEntity($oldEntities);

        $newBeginningLocationId = Uuid::uuid4();

        $newBeginningEntity = $game->createBeginningEntity(
            $game->getId(),
            $newBeginningLocationId,
            $this->varietyRepository->find($oldBeginningEntity->getVarietyId()),
            $oldBeginningEntity->getLabel(),
            $oldBeginningEntity->getIcon(),
            $this->varietyRepository,
            $this->resourceRepository
        );

        $game->restart();

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($game);
        $unitOfWork->save($newBeginningEntity);
        foreach ($oldEntities as $entity) {
            $unitOfWork->delete($entity);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
