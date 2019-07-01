<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\PlayerReadsWrittenWork;

use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\WrittenWorkRepository;
use Ramsey\Uuid\UuidInterface;

final class UseCase
{
    /** @var EntityRepository */
    private $entityRepository;

    /** @var WrittenWorkRepository */
    private $writtenWorkRepository;

    public function __construct(EntityRepository $entityRepository, WrittenWorkRepository $writtenWorkRepository)
    {
        $this->entityRepository = $entityRepository;
        $this->writtenWorkRepository = $writtenWorkRepository;
    }

    public function __invoke(UuidInterface $gameId, UuidInterface $actorId, UuidInterface $varietyId): Result
    {
        $writtenWork = $this->writtenWorkRepository->find($varietyId);

        if (is_null($writtenWork)) {
            return Result::notFound();
        }

        return Result::fromWrittenWork($writtenWork);
    }
}
