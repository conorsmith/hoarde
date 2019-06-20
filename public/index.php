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
    $entityRepository,
    $scavengingHaulRepository
);

$templateEngine = new ConorSmith\Hoarde\Infra\TemplateEngine;

$router = new League\Route\Router;

/**
 * ROUTES
 */

$router->get("/", new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage($templateEngine));

$router->post("/", new ConorSmith\Hoarde\Infra\Controller\GenerateNewGame(
    new \ConorSmith\Hoarde\UseCase\GameBegins\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $resourceRepository,
        $unitOfWorkProcessor
    )
));

$router->get("/{fileName}.js", new ConorSmith\Hoarde\Infra\Controller\CompileJsOutput);
$router->get("/main.css", new ConorSmith\Hoarde\Infra\Controller\CompileCssOutput);

$router->get("/{gameId}", new ConorSmith\Hoarde\Infra\Controller\ShowGame(
    $gameRepository,
    $entityRepository,
    $resourceRepository,
    $actionRepository,
    $varietyRepository,
    $sessionSegment
));

$router->post("/{gameId}/restart", new ConorSmith\Hoarde\Infra\Controller\RestartGame(
    new \ConorSmith\Hoarde\UseCase\GameRestarts\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $resourceRepository,
        $unitOfWorkProcessor
    )
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

$router->post("/{gameId}/{entityId}/scavenge", new ConorSmith\Hoarde\Infra\Controller\HaveEntityScavenge(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityScavenges\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/scavenge/{haulId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntityAddHaulToInventory(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityAddsScavengingHaul\UseCase(
        $entityRepository,
        $scavengingHaulRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/drop", new ConorSmith\Hoarde\Infra\Controller\HaveEntityDropItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityDiscardsItem\UseCase(
        $entityRepository,
        $varietyRepository
    )
));

$router->post("/{gameId}/transfer", new ConorSmith\Hoarde\Infra\Controller\TransferItems(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntitiesTransferItems\UseCase(
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/fetch-water", new ConorSmith\Hoarde\Infra\Controller\HaveEntityFetchWater(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityFetchesWater\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
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

$router->post("/{gameId}/{actorId}/sow/{targetId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntitySowPlot(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntitySowsPlot\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{actorId}/harvest/{targetId}", new ConorSmith\Hoarde\Infra\Controller\HaveEntityHarvestPlot(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityHarvestsPlot\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/settings", new ConorSmith\Hoarde\Infra\Controller\UpdateEntitySettings(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\PlayerRelabelsEntity\UseCase(
        $entityRepository
    )
));

/**
 * DISPATCH REQUEST
 */

try {
    $response = $router->dispatch(Zend\Diactoros\ServerRequestFactory::fromGlobals(
        $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
    ));
} catch (League\Route\Http\Exception\NotFoundException $e) {
    $response = (new ConorSmith\Hoarde\Infra\Controller\ShowNotFoundPage($templateEngine))();
}

(new Zend\HttpHandlerRunner\Emitter\SapiEmitter)->emit($response);
