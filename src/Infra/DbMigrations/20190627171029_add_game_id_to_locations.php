<?php

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class AddGameIdToLocations extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `locations`
            ADD COLUMN `game_id` varchar(36) NOT NULL
            AFTER `id`
        ");
    }
}
