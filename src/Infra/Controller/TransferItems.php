<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\UseCase\EntitiesTransferItems\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class TransferItems
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
        $manifests = json_decode($request->getBody()->getContents(), true);

        $result = $this->useCase->__invoke($gameId, $manifests);

        if (!$result->isSuccessful()) {
            $response = new Response;
            $response->withStatus(400);
            $response->getBody()->write($result->getMessage());
            return $response;
        }

        return new Response;
    }
}
