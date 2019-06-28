<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityTravels;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Direction;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Location;
use ConorSmith\Hoarde\Domain\LocationRepository;
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

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        LocationRepository $locationRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->locationRepository = $locationRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, Direction $direction): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);

        if (is_null($game)) {
            return Result::failed(GeneralResult::gameNotFound($gameId));
        }

        if (is_null($actor)) {
            return Result::failed(GeneralResult::entityNotFound($actorId, $gameId));
        }

        $location = $this->locationRepository->findInGame($actor->getLocationId(), $gameId);

        if (is_null($location)) {
            return Result::failed(GeneralResult::failed("Location {$actor->getLocationId()} was not found."));
        }

        $newCoordinates = $location->getCoordinates()->translate($direction);

        $newLocation = $this->locationRepository->findByCoordinates($newCoordinates, $gameId);

        if (is_null($newLocation)) {
            $newLocation = new Location(
                Uuid::uuid4(),
                $gameId,
                $newCoordinates,
                5
            );
        }

        $actor->travelTo($newLocation);

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($newLocation);

        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded($newLocation->getId());
    }
}
