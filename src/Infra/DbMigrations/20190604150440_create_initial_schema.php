<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class CreateInitialSchema extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE IF NOT EXISTS `entities` (
                `id` varchar(36) NOT NULL,
                `game_id` varchar(36) NOT NULL,
                `variety_id` varchar(36) NOT NULL,
                `label` varchar(256) NOT NULL,
                `icon` varchar(256) NOT NULL,
                `intact` tinyint(1) NOT NULL DEFAULT '1',
                `is_constructed` tinyint(1) NOT NULL DEFAULT '1',
                `construction_level` int(11) NOT NULL DEFAULT '0'
            )
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `entity_inventory` (
                `entity_id` varchar(36) NOT NULL,
                `item_id` varchar(36) NOT NULL,
                `quantity` int(11) NOT NULL,
                PRIMARY KEY (`entity_id`,`item_id`)
            )
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `entity_resources` (
                `entity_id` varchar(36) NOT NULL,
                `resource_id` varchar(36) NOT NULL,
                `level` int(11) DEFAULT NULL,
                `last_consumed_variety_id` varchar(36) DEFAULT NULL,
                PRIMARY KEY (`entity_id`,`resource_id`)
            )
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `games` (
                `id` varchar(36) NOT NULL,
                `turn_index` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            )
        ");

        $this->execute("
            CREATE TABLE IF NOT EXISTS `scavenging_haul_items` (
                `haul_id` varchar(36) NOT NULL,
                `variety_id` varchar(36) NOT NULL,
                `quantity` int(11) NOT NULL,
                PRIMARY KEY (`haul_id`,`variety_id`)
            )
        ");
    }
}
