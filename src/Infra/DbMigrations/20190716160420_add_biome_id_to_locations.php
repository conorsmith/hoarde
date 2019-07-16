<?php

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class AddBiomeIdToLocations extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `locations`
            ADD COLUMN `biome_id` varchar(36) NOT NULL
            AFTER `game_id`
        ");

        $this->execute("UPDATE `locations` SET biome_id = 'c923fe24-1d2d-4872-a5df-98ba3b7fd2f6'");
    }
}
