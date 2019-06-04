<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\DbMigrations;

use Phinx\Migration\AbstractMigration as PhinxMigration;
use Phinx\Migration\MigrationInterface;
use RuntimeException;

abstract class AbstractMigration extends PhinxMigration
{
    private const ROLLBACK_WARNING_MESSAGE = "Do not rollback migrations. "
        . "Development work and automated tests should always reset to an empty database. "
        . "Rolling back production tables should be achieved by writing a new migration.";

    abstract public function up();

    final public function down()
    {
        throw new RuntimeException(self::ROLLBACK_WARNING_MESSAGE);
    }

    final public function preFlightCheck($direction = null)
    {
        if (method_exists($this, MigrationInterface::CHANGE)) {
            throw new RuntimeException(self::ROLLBACK_WARNING_MESSAGE);
        }

        parent::preFlightCheck($direction);
    }
}
