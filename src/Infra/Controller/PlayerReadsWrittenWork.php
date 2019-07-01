<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\UseCase\PlayerReadsWrittenWork\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\JsonResponse;

final class PlayerReadsWrittenWork
{
    /** @var UseCase */
    private $useCase;

    public function __construct(UseCase $useCase)
    {
        $this->useCase = $useCase;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $actorId = Uuid::fromString($args['actorId']);
        $varietyId = Uuid::fromString($args['varietyId']);

        $result = $this->useCase->__invoke($gameId, $actorId, $varietyId);

        if (!$result->foundWrittenWork()) {
            return new JsonResponse([
                'message' => "Written work not found.",
            ], 400);
        }

        return new JsonResponse([
            'title' => $result->getWrittenWork()->getTitle(),
            'body'  => $result->getWrittenWork()->getBody(),
        ]);
    }
}
