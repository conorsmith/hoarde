<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ItemRepository;
use Ramsey\Uuid\Uuid;

final class GenerateNewGame
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ItemRepository */
    private $itemRepo;

    public function __construct(GameRepository $gameRepo, EntityRepository $entityRepo, ItemRepository $itemRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->itemRepo = $itemRepo;
    }

    public function __invoke()
    {
        $newGame = new Game(
            $id = Uuid::uuid4(),
            0
        );
        $this->gameRepo->save($newGame);

        $newEntity = new Entity(
            Uuid::uuid4(),
            $id,
            true,
            [],
            []
        );
        $newEntity->reset($this->itemRepo);
        $this->entityRepo->save($newEntity);

        header("Location: /{$id}");
    }
}
