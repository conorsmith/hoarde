<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerSortsEntities;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use DomainException;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(EntityRepository $entityRepository, UnitOfWorkProcessor $unitOfWorkProcessor)
    {
        $this->entityRepository = $entityRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(UuidInterface $gameId, iterable $orderedEntityIds): Result
    {
        $orderedEntities = [];

        foreach ($orderedEntityIds as $entityId) {
            if (!$entityId instanceof UuidInterface) {
                throw new DomainException;
            }

            $entity = $this->entityRepository->findInGame($entityId, $gameId);

            if (is_null($entity)) {
                return Result::entityNotFound($entityId, $gameId);
            }

            $orderedEntities[] = $entity;
        }

        foreach ($orderedEntities as $index => $orderedEntity) {
            $orderedEntity->setOrderIndex($index);
        }

        $unitOfWork = new UnitOfWork;
        foreach ($orderedEntities as $orderedEntity) {
            $unitOfWork->save($orderedEntity);
        }
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded();
    }
}
