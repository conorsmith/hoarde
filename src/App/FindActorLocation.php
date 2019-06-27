<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\App;

use ConorSmith\Hoarde\Domain\EntityRepository;
use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class FindActorLocation
{
    /** @var EntityRepository */
    private $entityRepository;

    public function __construct(EntityRepository $entityRepository)
    {
        $this->entityRepository = $entityRepository;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId): UuidInterface
    {
        $actor = $this->entityRepository->findInGame($actorId, $gameId);

        if (is_null($actor)) {
            throw new RuntimeException;
        }

        return $actor->getLocationId();
    }
}
