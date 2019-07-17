<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityTravels;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\Direction;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\LocationRepository;
use ConorSmith\Hoarde\Domain\LocationTemplateRepository;
use ConorSmith\Hoarde\Infra\Repository\BiomeRepositoryConfig;
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
            $generatedLocation = $this->locationTemplateRepository->generateNewLocation($newCoordinates, $gameId);
            $newLocation = $generatedLocation->getLocation();
        }

        if ($newLocation->getBiomeId()->toString() === BiomeRepositoryConfig::OCEAN) {

            $unitOfWork = new UnitOfWork;

            $unitOfWork->save($newLocation);

            if (isset($generatedLocation)) {
                foreach ($generatedLocation->getEntities() as $generatedEntity) {
                    $unitOfWork->save($generatedEntity);
                }
            }

            $unitOfWork->commit($this->unitOfWorkProcessor);

            return Result::failed(GeneralResult::failed("{$actor->getLabel()} cannot travel through the ocean."));
        }

        $actor->travelTo($newLocation);

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);
        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }
        $unitOfWork->save($game);

        $unitOfWork->save($newLocation);

        if (isset($generatedLocation)) {
            foreach ($generatedLocation->getEntities() as $generatedEntity) {
                $unitOfWork->save($generatedEntity);
            }
        }

        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded($newLocation->getId());
    }
}
