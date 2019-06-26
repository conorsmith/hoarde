<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class AddLocationIdToEntities extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `entities`
            ADD COLUMN `location_id` varchar(36) NOT NULL
            AFTER `game_id`
        ");
    }
}
