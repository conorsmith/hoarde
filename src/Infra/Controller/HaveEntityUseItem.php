<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use Aura\Session\Segment;
use ConorSmith\Hoarde\Domain\Entity;
use ConorSmith\Hoarde\Domain\EntityRepository;
use ConorSmith\Hoarde\Domain\GameRepository;
use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use Zend\Diactoros\Response;

final class HaveEntityUseItem
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

        $game = $this->gameRepo->find($gameId);
        $entityIds = $this->gameRepo->findEntityIds($gameId);
        $entity = $this->entityRepo->find($entityIds[0]);

        $itemId = Uuid::fromString($_POST['item']);

        if (in_array(strval($itemId), [
            VarietyRepositoryConfig::BUCKET,
            VarietyRepositoryConfig::ROPE,
        ])) {
            $this->session->setFlash(
                "danger",
                "That does nothing"
            );

            $response = new Response;
            $response = $response->withHeader("Location", "/{$gameId}");
            return $response;
        }

        if ($itemId->equals(Uuid::fromString(VarietyRepositoryConfig::SHOVEL))) {
            $usedItem = $this->varietyRepo->find($itemId)
                ->createItemWithQuantity(1);

            $hasRope = false;
            $hasBucket = false;

            foreach ($entity->getInventory() as $item) {
                if ($item->getVariety()->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::ROPE))) {
                    $hasRope = true;
                } elseif ($item->getVariety()->getId()->equals(Uuid::fromString(VarietyRepositoryConfig::BUCKET))) {
                    $hasBucket = true;
                }
            }

            if (!$hasRope || !$hasBucket) {
                $this->session->setFlash(
                    "danger",
                    "Cannot dig a well without a rope and bucket"
                );

                $response = new Response;
                $response = $response->withHeader("Location", "/{$gameId}");
                return $response;
            }

            $well = new Entity(
                Uuid::uuid4(),
                $gameId,
                Uuid::fromString(VarietyRepositoryConfig::WELL),
                "Well",
                "tint",
                true,
                [],
                []
            );

            $this->entityRepo->save($well);

            $entity->dropItem(Uuid::fromString(VarietyRepositoryConfig::ROPE), 1);
            $entity->dropItem(Uuid::fromString(VarietyRepositoryConfig::BUCKET), 1);
            $entity->wait();

            $this->entityRepo->save($entity);

        } else {
            $usedItem = $entity->useItem($itemId);
            $this->entityRepo->save($entity);
        }

        if ($itemId->equals(Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE))) {
            $crate = new Entity(
                Uuid::uuid4(),
                $gameId,
                Uuid::fromString(VarietyRepositoryConfig::WOODEN_CRATE),
                $usedItem->getVariety()->getLabel(),
                $usedItem->getVariety()->getIcon(),
                true,
                [],
                []
            );

            $this->entityRepo->save($crate);
        }

        $game->proceedToNextTurn();
        $this->gameRepo->save($game);

        if (!$entity->isIntact()) {
            $this->session->setFlash("danger", "{$entity->getLabel()} has expired");
        }

        $response = new Response;
        $response = $response->withHeader("Location", "/{$gameId}");
        return $response;
    }
}
