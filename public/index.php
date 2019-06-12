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

$gameRepository = new ConorSmith\Hoarde\Infra\Repository\GameRepositoryDb($db);

$resourceRepository = new ConorSmith\Hoarde\Infra\Repository\ResourceRepositoryConfig;

$actionRepository = new ConorSmith\Hoarde\Infra\Repository\ActionRepositoryConfig;

$varietyRepository = new ConorSmith\Hoarde\Infra\Repository\VarietyRepositoryConfig(
    $resourceRepository,
    $actionRepository
);

$entityRepository = new ConorSmith\Hoarde\Infra\Repository\EntityRepositoryDb(
    $db,
    $varietyRepository,
    $resourceRepository
);

$scavengingHaulRepository = new \ConorSmith\Hoarde\Infra\Repository\ScavengingHaulRepositoryDb(
    $db,
    $varietyRepository
);

$unitOfWorkProcessor = new ConorSmith\Hoarde\Infra\UnitOfWorkProcessorDb(
    $db,
    $gameRepository,
    $entityRepository
);

$router = new League\Route\Router;

/**
 * ROUTES
 */

$router->get("/", new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage);

$router->post("/", new ConorSmith\Hoarde\Infra\Controller\GenerateNewGame(
    $gameRepository,
    $entityRepository,
    $varietyRepository,
    $resourceRepository));

$router->get("/{fileName}.js", new ConorSmith\Hoarde\Infra\Controller\CompileJsOutput);
$router->get("/main.css", new ConorSmith\Hoarde\Infra\Controller\CompileCssOutput);

$router->get("/{gameId}", new ConorSmith\Hoarde\Infra\Controller\ShowGame(
    $gameRepository,
    $entityRepository,
    $resourceRepository,
    $actionRepository,
    $sessionSegment
));

$router->post("/{gameId}/restart", new ConorSmith\Hoarde\Infra\Controller\RestartGame(
    $gameRepository,
    $entityRepository,
    $varietyRepository,
    $resourceRepository
));

$router->post("/{gameId}/{entityId}/wait", new ConorSmith\Hoarde\Infra\Controller\HaveEntityWait(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityWaits\UseCase(
        $gameRepository,
        $entityRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/use", new ConorSmith\Hoarde\Infra\Controller\HaveEntityUseItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityUsesItem\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/consume", new ConorSmith\Hoarde\Infra\Controller\HaveEntityConsumeResource(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem\UseCase(
        $entityRepository,
        $resourceRepository
    )
));

$router->post("/{gameId}/scavenge", new ConorSmith\Hoarde\Infra\Controller\HaveEntityScavenge(
    $gameRepository,
    $entityRepository,
    $scavengingHaulRepository,
    $varietyRepository,
    $sessionSegment
));

$router->post("/{gameId}/scavenge/{haulId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntityAddHaulToInventory(
    $gameRepository,
    $entityRepository,
    $scavengingHaulRepository,
    $varietyRepository,
    $sessionSegment
));

$router->post("/{gameId}/drop", new ConorSmith\Hoarde\Infra\Controller\HaveEntityDropItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityDiscardsItem\UseCase(
        $entityRepository,
        $varietyRepository
    )
));

$router->post("/{gameId}/transfer", new ConorSmith\Hoarde\Infra\Controller\TransferItems(
    $gameRepository,
    $entityRepository,
    $varietyRepository,
    $sessionSegment
));

$router->post("/{gameId}/fetch-water", new ConorSmith\Hoarde\Infra\Controller\FetchWater(
    $gameRepository,
    $entityRepository,
    $varietyRepository,
    $sessionSegment
));

$router->post("/{gameId}/{actorId}/construct", new ConorSmith\Hoarde\Infra\Controller\HaveEntityBeginConstruction(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityBeginsConstructingEntity\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{actorId}/construct/{targetId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntityContinueConstruct(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityContinuesConstructingEntity\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/settings", new ConorSmith\Hoarde\Infra\Controller\UpdateEntitySettings(
    $gameRepository,
    $entityRepository,
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
