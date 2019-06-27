<?php

use ConorSmith\Hoarde\Infra\DbMigrations\AbstractMigration;
use Ramsey\Uuid\Uuid;

class AddLocationsToExistingGames extends AbstractMigration
{
    public function up()
    {
        $games = $this->fetchAll("SELECT * FROM games");

        foreach ($games as $game) {
            $gameId = $game['id'];
            $locationId = Uuid::uuid4();

            $locationsTable = $this->table("locations");
            $locationsTable->insert([
                'id'               => $locationId,
                'game_id'          => $gameId,
                'x_coordinate'     => 0,
                'y_coordinate'     => 0,
                'scavenging_level' => 5,
            ]);
            $locationsTable->saveData();

            $this->execute("UPDATE entities SET location_id = '{$locationId}' WHERE game_id = '{$gameId}'");
        }
    }
}
