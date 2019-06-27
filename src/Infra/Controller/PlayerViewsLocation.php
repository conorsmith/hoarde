<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Infra\Presentation\Action;
use ConorSmith\Hoarde\Infra\Presentation\Alert;
use ConorSmith\Hoarde\Infra\Presentation\BlueprintFactory;
use ConorSmith\Hoarde\Infra\Presentation\EntityFactory;
use ConorSmith\Hoarde\Infra\Presentation\Game;
use ConorSmith\Hoarde\Infra\TemplateEngine;
use ConorSmith\Hoarde\UseCase\PlayerViewsLocation\UseCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\HtmlResponse;

final class PlayerViewsLocation
{
    /** @var UseCase */
    private $useCase;

    /** @var Segment */
    private $session;

    /** @var TemplateEngine */
    private $templateEngine;

    /** @var EntityFactory */
    private $entityPresentationFactory;

    /** @var BlueprintFactory */
    private $blueprintPresentationFactory;

    public function __construct(
        UseCase $useCase,
        Segment $session,
        TemplateEngine $templateEngine,
        EntityFactory $entityPresentationFactory,
        BlueprintFactory $blueprintPresentationFactory
    ) {
        $this->useCase = $useCase;
        $this->session = $session;
        $this->templateEngine = $templateEngine;
        $this->entityPresentationFactory = $entityPresentationFactory;
        $this->blueprintPresentationFactory = $blueprintPresentationFactory;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);
        $locationId = Uuid::fromString($args['locationId']);

        $result = $this->useCase->__invoke($gameId, $locationId);

        if (!$result->isSuccessful()) {
            return new HtmlResponse($this->templateEngine->render("error.php", [
                'message' => $result->getMessage(),
            ]));
        }

        $gameState = $result->getGameState();

        return new HtmlResponse($this->templateEngine->render("game.php", [
            'human'         => $this->entityPresentationFactory->createEntity(
                $gameState->getHuman(),
                $gameState->getHuman()->getId(),
                $gameState->getEntities()
            ),
            'isIntact'      => $gameState->getHuman()->isIntact(),
            'alert'         => Alert::fromSession($this->session),
            'game'          => new Game(
                $gameState->getGame()
            ),
            'entities'      => array_map(
                function (Entity $entity) use ($gameState) {
                    return $this->entityPresentationFactory->createEntity(
                        $entity,
                        $gameState->getHuman()->getId(),
                        $gameState->getEntities()
                    );
                },
                $gameState->getEntities()
            ),
            'actions'       => Action::createMany(
                $gameState->getActions()
            ),
            'constructions' => $this->blueprintPresentationFactory->createFromVarieties(
                $gameState->getVarietiesWithBlueprints()
            ),
        ]));
    }
}
