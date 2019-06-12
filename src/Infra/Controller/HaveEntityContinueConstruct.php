<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Zend\Diactoros\Response;

final class HaveEntityContinueConstruct
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

        $targetId = Uuid::fromString($args['targetId']);
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
