<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class AddOrderIndexToEntities extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            ALTER TABLE `entities`
            ADD COLUMN `order_index` INT(11) NOT NULL DEFAULT '0'
            AFTER `icon`
        ");
    }
}
