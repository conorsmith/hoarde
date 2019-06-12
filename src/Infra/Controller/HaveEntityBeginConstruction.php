<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Zend\Diactoros\Response;

final class HaveEntityBeginConstruction
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
}
