<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use DomainException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class TransferItems
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var Segment */
    private $session;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        VarietyRepository $varietyRepo,
        Segment $session
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->varietyRepo = $varietyRepo;
        $this->session = $session;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);

        $manifests = json_decode($request->getBody()->getContents(), true);

        if (count($manifests) !== 2) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("TransferController request must contain exactly two transfer manifests");
            return $response;
        }

        $manifestA = $manifests[0];
        $manifestB = $manifests[1];

        $entityA = $this->entityRepo->find(Uuid::fromString($manifestA['entityId']));
        $entityB = $this->entityRepo->find(Uuid::fromString($manifestB['entityId']));

        $inventoryA = $entityA->getInventory();
        $inventoryB = $entityB->getInventory();

        if (!$entityA->getGameId()->equals($gameId)
            || !$entityB->getGameId()->equals($gameId)
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("TransferController request must be for entities from this game");
            return $response;
        }

        try {
            foreach ($manifestA['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $inventoryA->decrementItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $inventoryB->incrementItemQuantity(
                        Uuid::fromString($item['varietyId']),
                        intval($item['quantity']),
                        $this->varietyRepo
                    );
                }
            }

            foreach ($manifestB['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $inventoryB->decrementItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $inventoryA->incrementItemQuantity(
                        Uuid::fromString($item['varietyId']),
                        intval($item['quantity']),
                        $this->varietyRepo
                    );
                }
            }
        } catch (DomainException $e) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write($e->getMessage());
            return $response;
        }

        $this->entityRepo->save($entityA);
        $this->entityRepo->save($entityB);

        $response = new Response;
        return $response;
    }
}
