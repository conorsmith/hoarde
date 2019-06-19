<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerRelabelsEntity;

use ConorSmith\Hoarde\App\Result;
use ConorSmith\Hoarde\Domain\EntityRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $entityId, string $label): Result
    {
        $entity = $this->entityRepository->findInGame($entityId, $gameId);

        if (is_null($entity)) {
            return Result::entityNotFound($entityId, $gameId);
        }

        if (strlen($label) === 0) {
            return Result::failed("Entity label cannot be empty");
        }

        $entity->relabel($label);

        $this->entityRepository->save($entity);

        return Result::succeeded();
    }
}
