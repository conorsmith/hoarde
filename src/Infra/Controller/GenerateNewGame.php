<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\Construction;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\Inventory;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class GenerateNewGame
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var ResourceRepository */
    private $resourceRepo;

    public function __construct(GameRepository $gameRepo, EntityRepository $entityRepo, VarietyRepository $varietyRepo, ResourceRepository $resourceRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->varietyRepo = $varietyRepo;
        $this->resourceRepo = $resourceRepo;
    }

    public function __invoke(ServerRequestInterface $request): ResponseInterface
    {
        $newGame = new Game(
            $id = Uuid::uuid4(),
            0
        );
        $this->gameRepo->save($newGame);

        $variety = $this->varietyRepo->find(Uuid::fromString(VarietyRepositoryConfig::HUMAN));

        $newEntity = new Entity(
            $newEntityId = Uuid::uuid4(),
            $id,
            Uuid::fromString(VarietyRepositoryConfig::HUMAN),
            $request->getParsedBody()['label'],
            $request->getParsedBody()['icon'],
            true,
            Construction::constructed(),
            [],
            Inventory::empty($newEntityId, $variety->getInventoryCapacity())
        );
        $newEntity->reset($this->varietyRepo, $this->resourceRepo);
        $this->entityRepo->save($newEntity);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$id}");
        return $response;
    }
}
