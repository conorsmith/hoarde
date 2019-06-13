<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\UseCase\EntityAddsScavengingHaul\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

class HaveEntityAddHaulToInventory
{
    /** @var Segment */
    private $session;

    /** @var UseCase */
    private $useCase;

    public function __construct(
        Segment $session,
        UseCase $useCase
    ) {
        $this->session = $session;
        $this->useCase = $useCase;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $entityId = Uuid::fromString($args['entityId']);
        $haulId = Uuid::fromString($args['haulId']);

        $body = json_decode($request->getBody()->getContents(), true);
        $selectedItems = $body['selectedItems'];
        $modifiedInventory = $body['modifiedInventory'];

        $result = $this->useCase->__invoke($gameId, $entityId, $haulId, $selectedItems, $modifiedInventory);

        if (!$result->isSuccessful()) {
            $response = new Response;
            $response->getBody()->write($result->getMessage());
            return $response;
        }

        $response = new Response;
        return $response;
    }
}
