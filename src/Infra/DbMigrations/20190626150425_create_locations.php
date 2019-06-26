<?php
declare(strict_types=1);

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;

class CreateLocations extends AbstractMigration
{
    public function up()
    {
        $this->execute("
            CREATE TABLE `locations` (
                `id` varchar(36) NOT NULL,
                `x_coordinate` int(11) NOT NULL DEFAULT '0',
                `y_coordinate` int(11) NOT NULL DEFAULT '0',
                `scavenging_level` int(11) NOT NULL DEFAULT '0',
                PRIMARY KEY (`id`)
            )
        ");
    }
}
