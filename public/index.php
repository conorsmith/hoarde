<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

$db = Doctrine\DBAL\DriverManager::getConnection([
    'dbname'   => "hoarde",
    'user'     => "hoarde",
    'password' => "password",
    'host'     => "localhost",
    'driver'   => "pdo_mysql",
]);

$gameRepo = new ConorSmith\Hoarde\Infra\GameRepositoryDb($db);

$gameId = Ramsey\Uuid\Uuid::fromString("e23df13a-c3da-40d7-862c-50011d5d216a");
$entityId = Ramsey\Uuid\Uuid::fromString("a8e61eeb-91d5-409d-ad9e-3a5fd51fb072");
$resourceId = Ramsey\Uuid\Uuid::fromString("9972c015-842a-4601-8fb2-c900e1a54177");

$game = $gameRepo->find($gameId);

$row = $db->fetchAssoc("SELECT * FROM entity_resources WHERE entity_id = ? AND resource_id = ?", [
    strval($entityId),
    strval($resourceId),
]);

$resourceLevel = new ConorSmith\Hoarde\Domain\ResourceLevel($resourceId, intval($row['level']));
$entity = new ConorSmith\Hoarde\Domain\Entity($entityId, [$resourceLevel]);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if ($_POST['action'] === "restart") {
        $game->restart();

        $db->update("games", [
            'turn_index' => $game->getTurnIndex(),
        ], [
            'id' => $game->getId(),
        ]);

        foreach ($entity->getResourceLevels() as $resourceLevel) {
            $resourceLevel = new ConorSmith\Hoarde\Domain\ResourceLevel(
                $resourceLevel->getResourceId(),
                3
            );

            $db->update("entity_resources", [
                'level' => $resourceLevel->getValue(),
            ], [
                'entity_id'   => strval($entity->getId()),
                'resource_id' => strval($resourceLevel->getResourceId()),
            ]);
        }

    } elseif ($_POST['action'] === "wait") {
        $game->proceedToNextTurn();

        $db->update("games", [
            'turn_index' => $game->getTurnIndex(),
        ], [
            'id' => $game->getId(),
        ]);

        foreach ($entity->getResourceLevels() as $resourceLevel) {
            $resourceLevel = $resourceLevel->consume();

            $db->update("entity_resources", [
                'level' => $resourceLevel->getValue(),
            ], [
                'entity_id'   => strval($entity->getId()),
                'resource_id' => strval($resourceLevel->getResourceId()),
            ]);
        }

    } elseif ($_POST['action'] === "gather") {
        $game->proceedToNextTurn();

        $db->update("games", [
            'turn_index' => $game->getTurnIndex(),
        ], [
            'id' => $game->getId(),
        ]);

        foreach ($entity->getResourceLevels() as $resourceLevel) {
            $resourceLevel = $resourceLevel->replenish();

            $db->update("entity_resources", [
                'level' => $resourceLevel->getValue(),
            ], [
                'entity_id'   => strval($entity->getId()),
                'resource_id' => strval($resourceLevel->getResourceId()),
            ]);
        }
    }

    header("Location: /");
}

$turnIndex = $game->getTurnIndex();
$level = $resourceLevel->getValue();

$resourceLevels = [];
foreach ($entity->getResourceLevels() as $resourceLevel) {
    $resourceLevels[] = $resourceLevel->getValue();
}

include __DIR__ . "/../src/index.php";
