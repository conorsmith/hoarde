<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class HaveEntityConstruct
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $game = $this->gameRepo->find($gameId);

        $actor = $this->entityRepo->find(Uuid::fromString($_POST['actorId']));

        if (array_key_exists('targetId', $_POST)) {
            return $this->continueConstruction($actor, $game);
        } else {
            return $this->beginConstruction($actor, $game);
        }
    }

    private function beginConstruction(Entity $actor, Game $game): ResponseInterface
    {
        $hasShovel = false;
        $hasRope = false;
        $hasBucket = false;

        foreach ($actor->getInventory() as $item) {
            if ($item->getVariety()->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::ROPE))) {
                $hasRope = true;
            } elseif ($item->getVariety()->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::BUCKET))) {
                $hasBucket = true;
            } elseif ($item->getVariety()->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::SHOVEL))) {
                $hasShovel = true;
            }
        }

        if (!$hasShovel || !$hasRope || !$hasBucket) {
            $this->session->setFlash(
                "danger",
                "Cannot dig a well without a shovel, a rope and a bucket"
            );

            $response = new Response;
            $response = $response->withHeader("Location", "/{$game->getId()}");
            return $response;
        }

        $well = new Entity(
            Uuid::uuid4(),
            $game->getId(),
            Uuid::fromString(VarietyRepositoryConfig::WELL),
            "Well",
            "tint",
            true,
            new Construction(
                false,
                9,
                10
            ),
            [],
            []
        );

        $this->entityRepo->save($well);

        $actor->dropItem(Uuid::fromString(VarietyRepositoryConfig::ROPE), 1);
        $actor->dropItem(Uuid::fromString(VarietyRepositoryConfig::BUCKET), 1);
        $actor->wait();

        $this->entityRepo->save($actor);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$actor->isIntact()) {
            $this->session->setFlash("danger", "{$actor->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$game->getId()}");
        return $response;
    }

    private function continueConstruction(Entity $actor, Game $game): ResponseInterface
    {
        $target = $this->entityRepo->find(Uuid::fromString($_POST['targetId']));

        if (!$actor->getGameId()->equals($game->getId())
            || !$target->getGameId()->equals($game->getId())
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("HaveEntityConstruct request must be for an entity from this game");
            return $response;
        }

        $construction = $target->getConstruction();

        if ($construction->isConstructed()) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("Target entity has already been constructed");
            return $response;
        }

        $actor->construct($target);

        $this->entityRepo->save($actor);
        $this->entityRepo->save($target);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$actor->isIntact()) {
            $this->session->setFlash("danger", "{$actor->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$game->getId()}");
        return $response;
    }
}
