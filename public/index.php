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

$gameRepo = new ConorSmith\Hoarde\Infra\Repository\GameRepositoryDb($db);
$resourceRepo = new ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;
$varietyRepo = new ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig($resourceRepo);
$entityRepo = new ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb($db, $varietyRepo, $resourceRepo);
$scavengingHaulRepo = new \ConorSmith\Hoarde\Infra\Repository\ScavengingHaulRepositoryDb($db, $varietyRepo);

$showLandingPage = new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage;
$generateNewGame = new ConorSmith\Hoarde\Infra\Controller\GenerateNewGame($gameRepo, $entityRepo, $varietyRepo, $resourceRepo);
$restartGame = new ConorSmith\Hoarde\Infra\Controller\RestartGame($gameRepo, $entityRepo, $varietyRepo, $resourceRepo);
$haveEntityWait = new ConorSmith\Hoarde\Infra\Controller\HaveEntityWait($gameRepo, $entityRepo, $sessionSegment);
$haveEntityUseItem = new ConorSmith\Hoarde\Infra\Controller\HaveEntityUseItem($gameRepo, $entityRepo, $varietyRepo, $sessionSegment);
$haveEntityConsumeResource = new ConorSmith\Hoarde\Infra\Controller\HaveEntityConsumeResource($gameRepo, $entityRepo, $sessionSegment);
$haveEntityScavenge = new ConorSmith\Hoarde\Infra\Controller\HaveEntityScavenge($gameRepo, $entityRepo, $scavengingHaulRepo, $varietyRepo, $sessionSegment);
$haveEntityAddHaulToInventory = new ConorSmith\Hoarde\Infra\Controller\HaveEntityAddHaulToInventory($gameRepo, $entityRepo, $scavengingHaulRepo, $varietyRepo, $sessionSegment);
$haveEntityDropItem = new ConorSmith\Hoarde\Infra\Controller\HaveEntityDropItem($gameRepo, $entityRepo, $sessionSegment);
$showGame = new ConorSmith\Hoarde\Infra\Controller\ShowGame($gameRepo, $entityRepo, $resourceRepo, $sessionSegment);
$transferItems = new ConorSmith\Hoarde\Infra\Controller\TransferItems($gameRepo, $entityRepo, $varietyRepo, $sessionSegment);
$fetchWater = new ConorSmith\Hoarde\Infra\Controller\FetchWater($gameRepo, $entityRepo, $varietyRepo, $sessionSegment);
$showNotFoundPage = new ConorSmith\Hoarde\Infra\Controller\ShowNotFoundPage;

$router = new League\Route\Router;

$router->get("/", $showLandingPage);
$router->post("/", $generateNewGame);

$router->get("/{gameId}", $showGame);
$router->post("/{gameId}/restart", $restartGame);
$router->post("/{gameId}/wait", $haveEntityWait);
$router->post("/{gameId}/use", $haveEntityUseItem);
$router->post("/{gameId}/consume", $haveEntityConsumeResource);
$router->post("/{gameId}/scavenge", $haveEntityScavenge);
$router->post("/{gameId}/scavenge/{haulId}", $haveEntityAddHaulToInventory);
$router->post("/{gameId}/drop", $haveEntityDropItem);
$router->post("/{gameId}/transfer", $transferItems);
$router->post("/{gameId}/fetch-water", $fetchWater);

try {
    $response = $router->dispatch(Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    ));
} catch (League\Route\Http\Exception\NotFoundException $e) {
    $response = $showNotFoundPage();
}

(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
