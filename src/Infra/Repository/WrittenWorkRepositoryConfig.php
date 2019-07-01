<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Repository;

use ConorSmith\Hoarde\Domain\VarietyRepository;
use ConorSmith\Hoarde\Domain\WrittenWork;
use ConorSmith\Hoarde\Domain\WrittenWorkRepository;
use Ramsey\Uuid\UuidInterface;

final class WrittenWorkRepositoryConfig implements WrittenWorkRepository
{
    private const CONFIGS = [
        VarietyRepositoryConfig::JOELS_NOTE => [
            'body' => "This is it...",
        ],
    ];

    /** @var VarietyRepository */
    private $varietyRepository;

    public function __construct(VarietyRepository $varietyRepository)
    {
        $this->varietyRepository = $varietyRepository;
    }

    public function find(UuidInterface $varietyId): ?WrittenWork
    {
        $variety = $this->varietyRepository->find($varietyId);

        if (is_null($variety)) {
            return null;
        }

        if (!array_key_exists(strval($varietyId), self::CONFIGS)) {
            return null;
        }

        return new WrittenWork(
            $variety->getLabel(),
            self::CONFIGS[strval($varietyId)]['body']
        );
    }
}
