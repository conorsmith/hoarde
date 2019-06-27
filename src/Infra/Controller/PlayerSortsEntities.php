<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\App\FindActorLocation;
use ConorSmith\Hoarde\UseCase\PlayerSortsEntities\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response;

final class PlayerSortsEntities
{
    /** @var Segment */
    private $session;

    /** @var UseCase */
    private $useCase;

    /** @var FindActorLocation */
    private $findActorLocation;

    public function __construct(
        Segment $session,
        UseCase $useCase,
        FindActorLocation $findActorLocation
    ) {
        $this->session = $session;
        $this->useCase = $useCase;
        $this->findActorLocation = $findActorLocation;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $orderedEntityIds = array_map(
            function (string $id) {
                return Uuid::fromString($id);
            },
            json_decode($_POST['orderedEntityIds'], true)
        );

        $result = $this->useCase->__invoke($gameId, $orderedEntityIds);

        if (!$result->isSuccessful()) {
            $this->session->setFlash("danger", $result->getMessage());
        }

        $locationId = $this->findActorLocation->__invoke($gameId, $orderedEntityIds[0]);

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}/{$locationId}");
        return $response;
    }
}
