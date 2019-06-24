<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Presentation;

use ConorSmith\Hoarde\Domain\VarietyRepository;
use Ramsey\Uuid\Uuid;

final class BlueprintFactory
{
    /** @var VarietyRepository */
    private $varietyRepository;

    public function __construct(VarietyRepository $varietyRepository)
    {
        $this->varietyRepository = $varietyRepository;
    }

    public function createFromVarieties(iterable $varieties): iterable
    {
        $presentation = [];

        foreach ($varieties as $variety) {
            $blueprint = $variety->getBlueprint();
            $presentedTools = [];
            $presentedMaterials = [];

            foreach ($blueprint->getTools() as $toolVarietyId) {
                $tool = $this->varietyRepository->find(Uuid::fromString($toolVarietyId));
                $presentedTools[] = (object) [
                    'id'    => $toolVarietyId,
                    'label' => $tool->getLabel(),
                    'icon'  => $tool->getIcon(),
                ];
            }

            foreach ($blueprint->getMaterials() as $materialVarietyId => $requiredQuantity) {
                $material = $this->varietyRepository->find(Uuid::fromString($materialVarietyId));
                $presentedMaterials[] = (object) [
                    'id'       => $materialVarietyId,
                    'label'    => $material->getLabel(),
                    'icon'     => $material->getIcon(),
                    'quantity' => $requiredQuantity,
                ];
            }

            $presentation[] = (object) [
                'id'        => strval($variety->getId()),
                'label'     => $variety->getLabel(),
                'icon'      => $variety->getIcon(),
                'turns'     => $blueprint->getTurns(),
                'tools'     => $presentedTools,
                'materials' => $presentedMaterials,
            ];
        }

        return $presentation;
    }
}
