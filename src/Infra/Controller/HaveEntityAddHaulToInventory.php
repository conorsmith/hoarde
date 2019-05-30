<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ScavengingHaulRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

class HaveEntityAddHaulToInventory
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ScavengingHaulRepository */
    private $scavengedHaulRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ScavengingHaulRepository $scavengingHaulRepo,
        VarietyRepository $varietyRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->scavengedHaulRepo = $scavengingHaulRepo;
        $this->varietyRepo = $varietyRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $haulId = Uuid::fromString($args['haulId']);

        $selectedItems = json_decode($request->getBody()->getContents(), true)['selectedItems'];

        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $haul = $this->scavengedHaulRepo->find($haulId);

        foreach ($selectedItems as $varietyId => $quantity) {
            $haul->reduceItemQuantity(Uuid::fromString($varietyId), $quantity);
        }

        if (!$haul->isRetrievableBy($entity)) {
            $response = new Response;
            $response->getBody()->write("{$entity->getLabel()} cannot carry that much!");
            return $response;
        }

        $entity->addHaulToInventory($haul);

        $this->scavengedHaulRepo->delete($haul);
        $this->entityRepo->save($entity);

        $response = new Response;
        return $response;
    }
}
