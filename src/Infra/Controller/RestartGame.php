<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ItemRepository;
use Ramsey\Uuid\Uuid;

final class RestartGame
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
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $entity->reset($this->itemRepo);
        $this->entityRepo->save($entity);

        $game->restart();
        $this->gameRepo->save($game);

        header("Location: /{$gameId}");
    }
}
