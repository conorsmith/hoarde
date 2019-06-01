<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class RestartGame
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

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $entity->reset($this->varietyRepo, $this->resourceRepo);
        $this->entityRepo->save($entity);

        if (count($entityIds) > 1) {
            $crate = $this->entityRepo->find($entityIds[1]);
            $this->entityRepo->delete($crate);
        }

        $game->restart();
        $this->gameRepo->save($game);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
