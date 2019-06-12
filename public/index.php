<?php
declare(strict_types=1);

require_once __DIR__ . "/../vendor/autoload.php";

Dotenv\Dotenv::create(__DIR__ . "/..")->load();

/**
 * DEPENDENCIES
 */

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
$actionRepo = new ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig;
$varietyRepo = new ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig($resourceRepo, $actionRepo);
$entityRepo = new ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb($db, $varietyRepo, $resourceRepo);
$scavengingHaulRepo = new \ConorSmith\Hoarde\Infra\Repository\ScavengingHaulRepositoryDb($db, $varietyRepo);

$router = new League\Route\Router;

/**
 * ROUTES
 */

$router->get("/", new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage);

$router->post("/", new ConorSmith\Hoarde\Infra\Controller\GenerateNewGame(
    $gameRepo,
    $entityRepo,
    $varietyRepo,
    $resourceRepo));

$router->get("/{fileName}.js", new ConorSmith\Hoarde\Infra\Controller\CompileJsOutput);
$router->get("/main.css", new ConorSmith\Hoarde\Infra\Controller\CompileCssOutput);

$router->get("/{gameId}", new ConorSmith\Hoarde\Infra\Controller\ShowGame(
    $gameRepo,
    $entityRepo,
    $resourceRepo,
    $actionRepo,
    $sessionSegment
));

$router->post("/{gameId}/restart", new ConorSmith\Hoarde\Infra\Controller\RestartGame(
    $gameRepo,
    $entityRepo,
    $varietyRepo,
    $resourceRepo
));

$router->post("/{gameId}/wait", new ConorSmith\Hoarde\Infra\Controller\HaveEntityWait(
    $gameRepo,
    $entityRepo,
    $sessionSegment
));

$router->post("/{gameId}/use", new ConorSmith\Hoarde\Infra\Controller\HaveEntityUseItem(
    $gameRepo,
    $entityRepo,
    $varietyRepo,
    $sessionSegment
));

$router->post("/{gameId}/consume", new ConorSmith\Hoarde\Infra\Controller\HaveEntityConsumeResource(
    $gameRepo,
    $entityRepo,
    $sessionSegment
));

$router->post("/{gameId}/scavenge", new ConorSmith\Hoarde\Infra\Controller\HaveEntityScavenge(
    $gameRepo,
    $entityRepo,
    $scavengingHaulRepo,
    $varietyRepo,
    $sessionSegment
));

$router->post("/{gameId}/scavenge/{haulId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntityAddHaulToInventory(
    $gameRepo,
    $entityRepo,
    $scavengingHaulRepo,
    $varietyRepo,
    $sessionSegment
));

$router->post("/{gameId}/drop", new ConorSmith\Hoarde\Infra\Controller\HaveEntityDropItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityDiscardsItem\UseCase(
        $entityRepo,
        $varietyRepo
    )
));

$router->post("/{gameId}/transfer", new ConorSmith\Hoarde\Infra\Controller\TransferItems(
    $gameRepo,
    $entityRepo,
    $varietyRepo,
    $sessionSegment
));

$router->post("/{gameId}/fetch-water", new ConorSmith\Hoarde\Infra\Controller\FetchWater(
    $gameRepo,
    $entityRepo,
    $varietyRepo,
    $sessionSegment
));

$router->post("/{gameId}/construct", new ConorSmith\Hoarde\Infra\Controller\HaveEntityConstruct(
    $gameRepo,
    $entityRepo,
    $sessionSegment
));

$router->post("/{gameId}/{entityId}/settings", new ConorSmith\Hoarde\Infra\Controller\UpdateEntitySettings(
    $gameRepo,
    $entityRepo,
    $sessionSegment
));

/**
 * DISPATCH REQUEST
 */

try {
    $response = $router->dispatch(Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    ));
} catch (League\Route\Http\Exception\NotFoundException $e) {
    $response = (new ConorSmith\Hoarde\Infra\Controller\ShowNotFoundPage)();
}

(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
