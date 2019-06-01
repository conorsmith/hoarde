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

final class HaveEntityScavenge
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

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $haul = $entity->scavenge($this->varietyRepo, $this->gameRepo, $this->entityRepo);
        $this->entityRepo->save($entity);
        $this->scavengedHaulRepo->save($haul);

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        $transformedHaul = [
            'id' => $haul->getId(),
            'weight' => $haul->getWeight(),
            'items' => [],
        ];

        if ($haul->hasItems()) {
            foreach ($haul->getItems() as $item) {
                $transformedHaul['items'][] = [
                    'varietyId'     => $item->getVariety()->getId(),
                    'label'         => $item->getVariety()->getLabel(),
                    'weight'        => $item->getVariety()->getWeight(),
                    'quantity'      => $item->getQuantity(),
                    'icon'          => $item->getVariety()->getIcon(),
                ];
            }
        }

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "{$entity->getLabel()} has expired");
        }

        $response = new Response;
        $response->getBody()->write(json_encode([
            'haul' => $transformedHaul,
        ]));
        return $response;
    }
}
