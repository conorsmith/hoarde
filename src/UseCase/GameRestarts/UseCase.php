<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameRestarts;

use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Coordinates;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationRepository;
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

    /** @var LocationRepository */
    private $locationRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        LocationRepository $locationRepository,
        VarietyRepository $varietyRepository,
        ResourceRepository $resourceRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->locationRepository = $locationRepository;
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $oldEntities = $this->entityRepository->allInGame($gameId);
        $oldLocations = $this->locationRepository->allInGame($gameId);

        if (is_null($game)) {
            return Result::gameNotFound($gameId);
        }

        if (count($oldEntities) === 0) {
            return Result::failed("Game {$game->getId()} has no entities");
        }

        $oldBeginningEntity = $game->findBeginningEntity($oldEntities);

        $newBeginningLocation = new Location(
            $newBeginningLocationId = Uuid::uuid4(),
            $gameId,
            Coordinates::origin(),
            5
        );

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
        $unitOfWork->save($newBeginningLocation);
        foreach ($oldEntities as $entity) {
            $unitOfWork->delete($entity);
        }
        foreach ($oldLocations as $location) {
            $unitOfWork->delete($location);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded($newBeginningLocationId);
    }
}
