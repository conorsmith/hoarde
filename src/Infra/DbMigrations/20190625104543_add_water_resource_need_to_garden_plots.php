<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;
use ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;
use ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig;

class AddWaterResourceNeedToGardenPlots extends AbstractMigration
{
    public function up()
    {
        $waterResourceId = ResourceRepositoryConfig::WATER;
        $gardenPlotId = VarietyRepositoryConfig::GARDEN_PLOT;

        $this->execute("
            INSERT INTO `entity_resources` (`entity_id`, `resource_id`, `level`)
            SELECT id, '{$waterResourceId}', 12 FROM `entities` WHERE `variety_id` = '{$gardenPlotId}'
        ");
    }
}
