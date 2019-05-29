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
$itemRepo = new ConorSmith\Hoarde\Infra\Repository\ItemRepositoryConfig;
$entityRepo = new ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb($db, $itemRepo);
$resourceRepo = new ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;

$showLandingPage = new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage;
$generateNewGame = new ConorSmith\Hoarde\Infra\Controller\GenerateNewGame($gameRepo, $entityRepo, $itemRepo);
$restartGame = new ConorSmith\Hoarde\Infra\Controller\RestartGame($gameRepo, $entityRepo, $itemRepo);
$haveEntityWait = new ConorSmith\Hoarde\Infra\Controller\HaveEntityWait($gameRepo, $entityRepo, $sessionSegment);
$haveEntityUseItem = new ConorSmith\Hoarde\Infra\Controller\HaveEntityUseItem($gameRepo, $entityRepo, $sessionSegment);
$haveEntityScavenge = new ConorSmith\Hoarde\Infra\Controller\HaveEntityScavenge($gameRepo, $entityRepo, $itemRepo, $sessionSegment);
$showGame = new ConorSmith\Hoarde\Infra\Controller\ShowGame($gameRepo, $entityRepo, $resourceRepo, $sessionSegment);
$showNotFoundPage = new ConorSmith\Hoarde\Infra\Controller\ShowNotFoundPage;

$router = new League\Route\Router;

$router->get("/", $showLandingPage);
$router->post("/", $generateNewGame);

$router->get("/{gameId}", $showGame);
$router->post("/{gameId}", function (Psr\Http\Message\ServerRequestInterface $request) use (
    $restartGame,
    $haveEntityWait,
    $haveEntityUseItem,
    $haveEntityScavenge,
    $showNotFoundPage
) {
    if ($_POST['action'] === "restart") {
        $response = $restartGame();

    } elseif ($_POST['action'] === "wait") {
        $response = $haveEntityWait();

    } elseif ($_POST['action'] === "use") {
        $response = $haveEntityUseItem();

    } elseif ($_POST['action'] === "scavenge") {
        $response = $haveEntityScavenge();

    } else {
        $response = $showNotFoundPage();
    }

    return $response;
});

try {
    $response = $router->dispatch(Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    ));
} catch (League\Route\Http\Exception\NotFoundException $e) {
    $response = $showNotFoundPage();
}

(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
