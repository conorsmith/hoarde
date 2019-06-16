<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class CreateEntityInventoryEntities extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `entity_inventory_entities` (
                `entity_id` varchar(36) NOT NULL,
                `inventory_entity_id` varchar(36) NOT NULL,
                PRIMARY KEY (`entity_id`, `inventory_entity_id`)
            )
        ");
    }
}
