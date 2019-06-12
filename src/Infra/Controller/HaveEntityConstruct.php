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
use Ramsey\Uuid\UuidInterface;
use RuntimeException;
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

        $actor = $this->entityRepo->find(Uuid::fromString($args['actorId']));

        if (array_key_exists('targetId', $args)) {
            return $this->continueConstruction($actor, $game, Uuid::fromString($args['targetId']));
        } else {
            return $this->beginConstruction($actor, $game);
        }
    }

    private function beginConstruction(Entity $actor, Game $game): ResponseInterface
    {
        $constructionVarietyId = Uuid::fromString($_POST['constructionVarietyId']);

        if ($constructionVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            $tools = [
                VarietyRepositoryConfig::SHOVEL,
            ];

            $materials = [
                VarietyRepositoryConfig::ROPE   => 1,
                VarietyRepositoryConfig::BUCKET => 1,
            ];

            $constructedEntity = new Entity(
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
        } elseif ($constructionVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            $tools = [
                VarietyRepositoryConfig::HAMMER,
                VarietyRepositoryConfig::HAND_SAW,
            ];

            $materials = [
                VarietyRepositoryConfig::TIMBER => 10,
                VarietyRepositoryConfig::NAIL   => 60,
            ];

            $constructedEntity = new Entity(
                Uuid::uuid4(),
                $game->getId(),
                Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE),
                "Wooden Crate",
                "box",
                true,
                new Construction(
                    false,
                    2,
                    3
                ),
                [],
                []
            );
        } else {
            throw new RuntimeException("Invalid construction");
        }

        $actorInventory = $actor->getInventory();

        $meetsRequirements = true;

        foreach ($tools as $tool) {
            if (!$actorInventory->containsItem(Uuid::fromString($tool))) {
                $meetsRequirements = false;
            }
        }

        foreach ($materials as $material => $quantity) {
            if (!$actorInventory->containsItemAmountingToAtLeast(Uuid::fromString($material), $quantity)) {
                $meetsRequirements = false;
            }
        }

        if (!$meetsRequirements) {
            $this->session->setFlash("danger", "Construction requirements not met.");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$game->getId()}");
            return $response;
        }

        $this->entityRepo->save($constructedEntity);

        foreach ($materials as $varietyId => $quantity) {
            $actorInventory->discardItem(Uuid::fromString($varietyId), $quantity);
        }

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

    private function continueConstruction(Entity $actor, Game $game, UuidInterface $targetId): ResponseInterface
    {
        $constructionVarietyId = Uuid::fromString($_POST['constructionVarietyId']);

        if ($constructionVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WELL))) {
            $tools = [
                VarietyRepositoryConfig::SHOVEL,
            ];
        } elseif ($constructionVarietyId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            $tools = [
                VarietyRepositoryConfig::HAMMER,
                VarietyRepositoryConfig::HAND_SAW,
            ];
        } else {
            throw new RuntimeException("Invalid construction");
        }

        $target = $this->entityRepo->find($targetId);

        if (!$actor->getGameId()->equals($game->getId())
            || !$target->getGameId()->equals($game->getId())
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("HaveEntityConstruct request must be for an entity from this game");
            return $response;
        }

        $actorInventory = $actor->getInventory();

        $meetsRequirements = true;

        foreach ($tools as $tool) {
            if (!$actorInventory->containsItem(Uuid::fromString($tool))) {
                $meetsRequirements = false;
            }
        }

        if (!$meetsRequirements) {
            $this->session->setFlash("danger", "Construction requirements not met.");

            $response = new Response;
            $response = $response->withHeader("Location", "/{$game->getId()}");
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
