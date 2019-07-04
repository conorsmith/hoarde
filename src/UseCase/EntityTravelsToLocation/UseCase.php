<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityTravelsToLocation;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\LocationRepository;
use ConorSmith\Hoarde\Domain\LocationTemplateRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var LocationRepository */
    private $locationRepository;

    /** @var LocationTemplateRepository */
    private $locationTemplateRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        LocationRepository $locationRepository,
        LocationTemplateRepository $locationTemplateRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->locationRepository = $locationRepository;
        $this->locationTemplateRepository = $locationTemplateRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $targetLocationId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $actor = $this->entityRepository->findInGame($actorId, $gameId);
        $targetLocation = $this->locationRepository->findInGame($targetLocationId, $gameId);

        if (is_null($game)) {
            return Result::failed(GeneralResult::gameNotFound($gameId));
        }

        if (is_null($actor)) {
            return Result::failed(GeneralResult::entityNotFound($actorId, $gameId));
        }

        if (!$actor->isIntact()) {
            return Result::failed(GeneralResult::actorExpired($actor));
        }

        if (is_null($targetLocation)) {
            return Result::failed(GeneralResult::failed("Location {$targetLocationId} was not found."));
        }

        $startingLocation = $this->locationRepository->findInGame($actor->getLocationId(), $gameId);

        if (is_null($startingLocation)) {
            return Result::failed(GeneralResult::failed("Location {$actor->getLocationId()} was not found."));
        }

        $route = $startingLocation->getCoordinates()->generateRouteTo(
            $targetLocation->getCoordinates()
        );

        $generatedLocations = [];

        foreach ($route as $coordinates) {
            $locationEnRoute = $this->locationRepository->findByCoordinates($coordinates, $gameId);

            if (is_null($locationEnRoute)) {
                $generatedLocation = $this->locationTemplateRepository->generateNewLocation($coordinates, $gameId);
                $locationEnRoute = $generatedLocation->getLocation();
                $generatedLocations[] = $generatedLocation;
            }

            $actor->travelTo($locationEnRoute);

            $gameEntities = $game->proceedToNextTurn($this->entityRepository);

            if (!$actor->isIntact()) {
                break;
            }
        }

        $unitOfWork = new UnitOfWork;

        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($game);

        foreach ($generatedLocations as $generatedLocation) {
            $unitOfWork->save($generatedLocation->getLocation());
            foreach ($generatedLocation->getEntities() as $generatedEntity) {
                $unitOfWork->save($generatedEntity);
            }
        }

        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded($locationEnRoute->getId());
    }
}
