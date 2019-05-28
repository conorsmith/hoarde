<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

Dotenv\Dotenv::create(__DIR__ . "/..")->load();

$session = (new Aura\Session\SessionFactory)->newInstance($_COOKIE);
$sessionSegment = $session->getSegment("ConorSmith\\Hoarde");

$db = Doctrine\DBAL\DriverManager::getConnection([
    'dbname'   => getenv('DB_NAME'),
    'user'     => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'host'     => getenv('DB_HOST'),
    'driver'   => "pdo_mysql",
]);

$gameRepo = new ConorSmith\Hoarde\Infra\GameRepositoryDb($db);
$itemRepo = new ConorSmith\Hoarde\Infra\ItemRepositoryConfig;
$entityRepo = new ConorSmith\Hoarde\Infra\EntityRepositoryDb($db, $itemRepo);
$resourceRepo = new ConorSmith\Hoarde\Infra\ResourceRepositoryConfig;

if ($_SERVER['REQUEST_URI'] === "/") {
    if ($_SERVER['REQUEST_METHOD'] === "POST") {
        $newGame = new ConorSmith\Hoarde\Domain\Game(
            $id = Ramsey\Uuid\Uuid::uuid4(),
            0
        );
        $gameRepo->save($newGame);

        $newEntity = new \ConorSmith\Hoarde\Domain\Entity(
            Ramsey\Uuid\Uuid::uuid4(),
            $id,
            true,
            [],
            []
        );
        $newEntity->reset($itemRepo);
        $entityRepo->save($newEntity);

        header("Location: /{$id}");
    }

    include __DIR__ . "/../src/generate.php";
    return;
}

$gameId = Ramsey\Uuid\Uuid::fromString(substr($_SERVER['REQUEST_URI'], 1));

$game = $gameRepo->find($gameId);
$entityIds = $gameRepo->findEntityIds($gameId);
$entity = $entityRepo->find($entityIds[0]);

if ($_SERVER['REQUEST_METHOD'] === "POST") {

    if ($_POST['action'] === "restart") {
        $entity->reset($itemRepo);
        $entityRepo->save($entity);

        $game->restart();
        $gameRepo->save($game);

    } elseif ($_POST['action'] === "wait") {
        $entity->wait();
        $entityRepo->save($entity);

        $game->proceedToNextTurn();
        $gameRepo->save($game);

        if (!$entity->isIntact()) {
            $sessionSegment->setFlash("danger", "Entity has expired");
        }

    } elseif ($_POST['action'] === "use") {
        $itemId = Ramsey\Uuid\Uuid::fromString($_POST['item']);
        $usedItem = $entity->useItem($itemId);
        $entityRepo->save($entity);

        $game->proceedToNextTurn();
        $gameRepo->save($game);

        $sessionSegment->setFlash("info", "Entity consumed {$usedItem->getLabel()}");

        if (!$entity->isIntact()) {
            $sessionSegment->setFlash("danger", "Entity has expired");
        }

    } elseif ($_POST['action'] === "scavenge") {
        $scavengedItem = $entity->scavenge($itemRepo);
        $entityRepo->save($entity);

        $game->proceedToNextTurn();
        $gameRepo->save($game);

        if (is_null($scavengedItem)) {
            $sessionSegment->setFlash(
                "warning",
                "Entity failed to scavenge anything"
            );
        } else {
            $sessionSegment->setFlash(
                "success",
                "Entity scavenged {$scavengedItem->getLabel()} ({$scavengedItem->getQuantity()})"
            );
        }

        if (!$entity->isIntact()) {
            $sessionSegment->setFlash("danger", "Entity has expired");
        }
    }

    header("Location: /{$gameId}");
}

$danger = $sessionSegment->getFlash("danger");
$warning = $sessionSegment->getFlash("warning");
$success = $sessionSegment->getFlash("success");
$info = $sessionSegment->getflash("info");

$turnIndex = $game->getTurnIndex();

$resources = [];
foreach ($entity->getResourceLevels() as $resourceLevel) {
    $resources[] = [
        'label'        => $resourceRepo->find($resourceLevel->getResourceId())->getLabel(),
        'level'        => $resourceLevel->getValue(),
        'segmentWidth' => 100 / $resourceLevel->getMaximumValue(),
    ];
}

$inventory = [];
foreach ($entity->getInventory() as $item) {
    $inventory[] = [
        'id'       => $item->getId(),
        'label'    => $item->getLabel(),
        'quantity' => $item->getQuantity(),
    ];
}

$isIntact = $entity->isIntact();

include __DIR__ . "/../src/index.php";
