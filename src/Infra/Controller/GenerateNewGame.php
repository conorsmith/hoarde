<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\Game;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Psr\Http\Message\ResponseInterface;
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

    public function __construct(GameRepository $gameRepo, EntityRepository $entityRepo, VarietyRepository $varietyRepo)
    {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->varietyRepo = $varietyRepo;
    }

    public function __invoke(): ResponseInterface
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
        $newEntity->reset($this->varietyRepo);
        $this->entityRepo->save($newEntity);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$id}");
        return $response;
    }
}
