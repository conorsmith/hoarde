<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class HaveEntityScavenge
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        VarietyRepository $varietyRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->varietyRepo = $varietyRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $haul = $entity->scavenge($this->varietyRepo);
        $this->entityRepo->save($entity);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if ($haul->hasItem()) {

            $label = $haul->getItem()->getVariety()->getLabel();

            if ($haul->isRetrievable()) {
                $this->session->setFlash(
                    "success",
                    "{$entity->getLabel()} scavenged {$label} ({$haul->getItem()->getQuantity()})"
                );
            } else {
                $this->session->setFlash(
                    "danger",
                    "{$entity->getLabel()} could not add {$label} ({$haul->getItem()->getQuantity()}) to inventory"
                );
            }
        } else {
            $this->session->setFlash(
                "warning",
                "{$entity->getLabel()} failed to scavenge anything"
            );
        }

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "{$entity->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
