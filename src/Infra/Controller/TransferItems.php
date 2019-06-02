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
            $response->getBody()->write("Transfer request must contain exactly two transfer manifests");
            return $response;
        }

        $manifestA = $manifests[0];
        $manifestB = $manifests[1];

        $entityA = $this->entityRepo->find(Uuid::fromString($manifestA['entityId']));
        $entityB = $this->entityRepo->find(Uuid::fromString($manifestB['entityId']));

        if (!$entityA->getGameId()->equals($gameId)
            || !$entityB->getGameId()->equals($gameId)
        ) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write("Transfer request must be for entities from this game");
            return $response;
        }

        try {
            foreach ($manifestA['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $entityA->decrementInventoryItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $entityB->incrementInventoryItemQuantity(
                        Uuid::fromString($item['varietyId']),
                        intval($item['quantity']),
                        $this->varietyRepo
                    );
                }
            }

            foreach ($manifestB['items'] as $item) {
                if (intval($item['quantity']) > 0) {
                    $entityB->decrementInventoryItemQuantity(Uuid::fromString($item['varietyId']), intval($item['quantity']));
                    $entityA->incrementInventoryItemQuantity(
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
