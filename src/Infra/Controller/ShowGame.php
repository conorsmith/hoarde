<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\ActionRepository;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Presentation\Alert;
use ConorSmith\Hoarde\Infra\Presentation\BlueprintFactory;
use ConorSmith\Hoarde\Infra\Presentation\EntityFactory;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use ConorSmith\Hoarde\Infra\TemplateEngine;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use RuntimeException;
use Zend\Diactoros\Response;

final class ShowGame
{
    /** @var GameRepository */
    private $gameRepo;

    /** @var EntityRepository */
    private $entityRepo;

    /** @var ActionRepository */
    private $actionRepo;

    /** @var VarietyRepository */
    private $varietyRepo;

    /** @var Segment */
    private $session;

    /** @var TemplateEngine */
    private $templateEngine;

    /** @var EntityFactory */
    private $entityPresentationFactory;

    /** @var BlueprintFactory */
    private $blueprintPresentationFactory;

    public function __construct(
        GameRepository $gameRepo,
        EntityRepository $entityRepo,
        ActionRepository $actionRepo,
        VarietyRepository $varietyRepo,
        Segment $session,
        TemplateEngine $templateEngine,
        EntityFactory $entityPresentationFactory,
        BlueprintFactory $blueprintPresentationFactory
    ) {
        $this->gameRepo = $gameRepo;
        $this->entityRepo = $entityRepo;
        $this->actionRepo = $actionRepo;
        $this->varietyRepo = $varietyRepo;
        $this->session = $session;
        $this->templateEngine = $templateEngine;
        $this->entityPresentationFactory = $entityPresentationFactory;
        $this->blueprintPresentationFactory = $blueprintPresentationFactory;
    }

    public function __invoke(): ResponseInterface
    {
        $gameId = Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);

        $human = null;
        $entities = [];

        foreach ($entityIds as $entityId) {
            $entities[] = $this->entityRepo->find($entityId);
        }

        $human = $this->findHuman($entities);

        if (is_null($human)) {
            throw new RuntimeException("Game is missing human entity");
        }

        $body = $this->templateEngine->render("game.php", [
            'human'         => $this->entityPresentationFactory->createEntity($human, $entities),
            'isIntact'      => $human->isIntact(),
            'alert'         => Alert::fromSession($this->session),
            'game'          => new \ConorSmith\Hoarde\Infra\Presentation\Game($game),
            'entities'      => array_map(function (Entity $entity) use ($entities) {
                return $this->entityPresentationFactory->createEntity($entity, $entities);
            }, $entities),
            'actions'       => \ConorSmith\Hoarde\Infra\Presentation\Action::createMany($this->actionRepo->all()),
            'constructions' => $this->blueprintPresentationFactory->createFromVarieties(
                $this->varietyRepo->allWithBlueprints()
            ),
        ]);

        $response = new Response;
        $response->getBody()->write($body);
        return $response;
    }

    private function findHuman(iterable $entities): ?Entity
    {
        foreach ($entities as $entity) {
            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                return $entity;
            }
        }

        return null;
    }
}
