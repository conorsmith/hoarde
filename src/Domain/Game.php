<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Domain;

use ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;
use DomainException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Game
{
    /** @var UuidInterface */
    private $id;

    /** @var int */
    private $turnIndex;

    public function __construct(UuidInterface $id, int $turnIndex)
    {
        $this->id = $id;
        $this->turnIndex = $turnIndex;
    }

    public function getId(): UuidInterface
    {
        return $this->id;
    }

    public function getTurnIndex(): int
    {
        return $this->turnIndex;
    }

    public function proceedToNextTurn(EntityRepository $entityRepository): iterable
    {
        $this->turnIndex++;

        $entities = $entityRepository->allInGame($this->id);

        foreach ($entities as $entity) {
            $entity->proceedToNextTurn();
        }

        return $entities;
    }

    public function restart(): void
    {
        $this->turnIndex = 0;
    }

    public function createBeginningEntity(
        UuidInterface $gameId,
        Variety $variety,
        string $label,
        string $icon,
        VarietyRepository $varietyRepository,
        ResourceRepository $resourceRepository
    ): Entity {
        $beginningEntityId = Uuid::uuid4();

        $resourceNeeds = [
            ResourceRepositoryConfig::FOOD => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString(ResourceRepositoryConfig::FOOD)),
                3,
                0, // Hack
                60,
                null
            ),
            ResourceRepositoryConfig::WATER => new ResourceNeed(
                $resourceRepository->find(Uuid::fromString(ResourceRepositoryConfig::WATER)),
                3,
                0, // Hack
                100,
                null
            )
        ];

        $inventory = Inventory::empty($beginningEntityId, $variety);

        $inventory->addItem(
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::WATER_BOTTLE))
                ->createItemWithQuantity(8)
        );

        $inventory->addItem(
            $varietyRepository
                ->find(Uuid::fromString(VarietyRepositoryConfig::TINNED_STEW))
                ->createItemWithQuantity(3)
        );

        $beginningEntity = new Entity(
            $beginningEntityId,
            $gameId,
            Uuid::fromString(VarietyRepositoryConfig::HUMAN),
            $label,
            $icon,
            $isIntact = true,
            Construction::constructed(),
            $resourceNeeds,
            $inventory
        );

        return $beginningEntity;
    }

    public function findBeginningEntity(iterable $entities): Entity
    {
        foreach ($entities as $entity) {
            if (!$entity instanceof Entity) {
                throw new DomainException;
            }

            if ($entity->getVarietyId()->equals(Uuid::fromString(VarietyRepositoryConfig::HUMAN))) {
                return $entity;
            }
        }

        throw new DomainException;
    }
}
