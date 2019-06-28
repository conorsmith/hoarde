<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\EntityScavenges;

use ConorSmith\Hoarde\App\Result as GeneralResult;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\LocationRepository;
use ConorSmith\Hoarde\Domain\Resource;
use ConorSmith\Hoarde\Domain\RollTableFactory;
use ConorSmith\Hoarde\Domain\ScavengingHaul;
use ConorSmith\Hoarde\Domain\VarietyRepository;
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

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        LocationRepository $locationRepository,
        VarietyRepository $varietyRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->locationRepository = $locationRepository;
        $this->varietyRepository = $varietyRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId): Result
    {
        $game = $this->gameRepository->find($gameId);
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($game)) {
            return Result::failedBecause(GeneralResult::gameNotFound($gameId));
        }

        if (is_null($entity)) {
            return Result::failedBecause(GeneralResult::entityNotFound($entityId, $gameId));
        }

        $location = $this->locationRepository->findInGame($entity->getLocationId(), $gameId);

        if (is_null($location)) {
            return Result::failedBecause(GeneralResult::failed("Location {$entity->getLocationId()} was not found."));
        }

        if ($location->isScavengedClean()) {
            return Result::failedBecause(GeneralResult::failed("This location has been scavenged clean."));
        }

        $haul = $location
            ->scavenge(
                new RollTableFactory($this->varietyRepository),
                $entity
            )
            ->roll();

        $entity->wait();

        if (!$entity->isIntact()) {
            $haul = ScavengingHaul::empty();
        }

        $unitOfWork = new UnitOfWork;

        $gameEntities = $game->proceedToNextTurn($this->entityRepository);

        foreach ($gameEntities as $gameEntity) {
            $unitOfWork->save($gameEntity);
        }

        $unitOfWork->save($entity);
        $unitOfWork->save($haul);
        $unitOfWork->save($location);
        $unitOfWork->save($game);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        if (!$entity->isIntact()) {
            return Result::failedBecause(GeneralResult::actorExpired($entity));
        }

        return Result::succeeded($this->presentHaul($haul));
    }

    private function presentHaul(ScavengingHaul $haul): array
    {
        $presentation = [
            'id'     => $haul->getId(),
            'weight' => $haul->getWeight(),
            'items'  => [],
        ];

        if ($haul->hasItems()) {
            foreach ($haul->getItems() as $item) {
                $presentation['items'][] = [
                    'varietyId'     => $item->getVariety()->getId(),
                    'label'         => $item->getVariety()->getLabel(),
                    'weight'        => $item->getVariety()->getWeight(),
                    'quantity'      => $item->getQuantity(),
                    'icon'          => $item->getVariety()->getIcon(),
                    'resourceLabel' => implode(", ", array_map(function (Resource $resource) {
                        return $resource->getLabel();
                    }, $item->getVariety()->getResources())),
                    'description'   => nl2br($item->getVariety()->getDescription()),
                ];
            }
        }

        return $presentation;
    }
}
