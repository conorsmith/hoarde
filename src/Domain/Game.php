<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\App\UnitOfWork;
use Ramsey\Uuid\UuidInterface;

final class Game
{
    /** @var UuidInterface */
    private $id;

    /** @var int */
    private $turnIndex;

    public function __construct(UuidInterface $id, int $turnIndex)
    {
        $this->id = $id;
        $this->turnIndex = $turnIndex;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTurnIndex(): int
    {
        return $this->turnIndex;
    }

    public function proceedToNextTurn(EntityRepository $entityRepository, UnitOfWork $unitOfWork): void
    {
        $this->turnIndex++;

        $entities = $entityRepository->allInGame($this->id);

        foreach ($entities as $entity) {
            $entity->proceedToNextTurn($unitOfWork);
            $unitOfWork->save($entity);
        }
    }

    public function restart(): void
    {
        $this->turnIndex = 0;
    }
}
