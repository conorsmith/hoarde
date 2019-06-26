<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\UseCase\GameBegins;

use ConorSmith\Hoarde\App\UnitOfWork;
use ConorSmith\Hoarde\App\UnitOfWorkProcessor;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Ramsey\Uuid\Uuid;

final class UseCase
{
    /** @var GameRepository */
    private $gameRepository;

    /** @var EntityRepository */
    private $entityRepository;

    /** @var VarietyRepository */
    private $varietyRepository;

    /** @var ResourceRepository */
    private $resourceRepository;

    /** @var UnitOfWorkProcessor */
    private $unitOfWorkProcessor;

    public function __construct(
        GameRepository $gameRepository,
        EntityRepository $entityRepository,
        VarietyRepository $varietyRepository,
        ResourceRepository $resourceRepository,
        UnitOfWorkProcessor $unitOfWorkProcessor
    ) {
        $this->gameRepository = $gameRepository;
        $this->entityRepository = $entityRepository;
        $this->varietyRepository = $varietyRepository;
        $this->resourceRepository = $resourceRepository;
        $this->unitOfWorkProcessor = $unitOfWorkProcessor;
    }

    public function __invoke(string $beginningEntityLabel, string $beginningEntityIcon): Result
    {
        $newGame = new Game(
            $newGameId = Uuid::uuid4(),
            $turnIndex = 0
        );

        $variety = $this->varietyRepository->find(Uuid::fromString(VarietyRepositoryConfig::HUMAN));

        $beginningLocationId = Uuid::uuid4();

        $beginningEntity = $newGame->createBeginningEntity(
            $newGameId,
            $beginningLocationId,
            $variety,
            $beginningEntityLabel,
            $beginningEntityIcon,
            $this->varietyRepository,
            $this->resourceRepository
        );

        $unitOfWork = new UnitOfWork;
        $unitOfWork->save($newGame);
        $unitOfWork->save($beginningEntity);
        $unitOfWork->commit($this->unitOfWorkProcessor);

        return Result::succeeded($newGameId);
    }
}
