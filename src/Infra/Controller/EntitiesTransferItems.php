<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Transfer\Item;
use ConorSmith\Hoarde\Domain\Transfer\Manifest;
use ConorSmith\Hoarde\UseCase\EntitiesTransferItems\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\HtmlResponse;

final class EntitiesTransferItems
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

        if (count($manifests) !== 2) {
            return new HtmlResponse("A transfer requires exactly two manifests", 400);
        }

        $manifestAToB = $this->createManifestFromRequestData($manifests[0]);
        $manifestBToA = $this->createManifestFromRequestData($manifests[1]);

        $result = $this->useCase->__invoke($gameId, $manifestAToB, $manifestBToA);

        if (!$result->isSuccessful()) {
            return new HtmlResponse($result->getMessage(), 400);
        }

        return new HtmlResponse("");
    }

    private function createManifestFromRequestData(array $requestData): Manifest
    {
        $items = [];

        foreach ($requestData['items'] as $manifestItem) {
            $items[] = new Item(
                Uuid::fromString($manifestItem['varietyId']),
                intval($manifestItem['quantity'])
            );
        }

        return new Manifest(
            Uuid::fromString($requestData['entityId']),
            $items
        );
    }
}
