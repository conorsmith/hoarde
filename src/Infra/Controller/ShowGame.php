<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\ResourceRepository;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class ShowGame
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ResourceRepository */
    private $resourceRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ResourceRepository $resourceRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->resourceRepo = $resourceRepo;
        $this->session = $session;
    }

    public function __invoke(): ResponseInterface
    {
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $danger = $this->session->getFlash("danger");
        $warning = $this->session->getFlash("warning");
        $success = $this->session->getFlash("success");
        $info = $this->session->getflash("info");

        $turnIndex = $game->getTurnIndex();

        $resources = [];
        foreach ($entity->getResourceLevels() as $resourceLevel) {
            $resources[] = [
                'label'        => $this->resourceRepo->find($resourceLevel->getResourceId())->getLabel(),
                'level'        => $resourceLevel->getValue(),
                'segmentWidth' => 100 / $resourceLevel->getMaximumValue(),
            ];
        }

        $inventory = [];
        foreach ($entity->getInventory() as $item) {
            $inventory[] = [
                'id'       => $item->getId(),
                'label'    => $item->getLabel(),
                'quantity' => $item->getQuantity(),
            ];
        }

        $isIntact = $entity->isIntact();

        ob_start();

        include __DIR__ . "/../../index.php";

        $body = ob_get_contents();

        ob_end_clean();

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }
}
