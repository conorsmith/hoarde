<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ItemRepository;
use Ramsey\Uuid\Uuid;

final class HaveEntityScavenge
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ItemRepository */
    private $itemRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ItemRepository $itemRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->itemRepo = $itemRepo;
        $this->session = $session;
    }

    public function __invoke()
    {
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $scavengedItem = $entity->scavenge($this->itemRepo);
        $this->entityRepo->save($entity);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (is_null($scavengedItem)) {
            $this->session->setFlash(
                "warning",
                "Entity failed to scavenge anything"
            );
        } else {
            $this->session->setFlash(
                "success",
                "Entity scavenged {$scavengedItem->getLabel()} ({$scavengedItem->getQuantity()})"
            );
        }

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "Entity has expired");
        }

        header("Location: /{$gameId}");
    }
}
