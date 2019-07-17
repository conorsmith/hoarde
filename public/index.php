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

$locationRepository = new ConorSmith\Hoarde\Infra\Repository\LocationRepositoryDb($db);

$scavengingHaulRepository = new \ConorSmith\Hoarde\Infra\Repository\ScavengingHaulRepositoryDb(
    $db,
    $varietyRepository
);

$locationTemplateRepository = new ConorSmith\Hoarde\Infra\Repository\LocationTemplateRepositoryConfig(
    $varietyRepository,
    $resourceRepository
);

$unitOfWorkProcessor = new ConorSmith\Hoarde\Infra\UnitOfWorkProcessorDb(
    $db,
    $gameRepository,
    $entityRepository,
    $locationRepository,
    $scavengingHaulRepository
);

$findActorLocation = new ConorSmith\Hoarde\App\FindActorLocation($entityRepository);

$templateEngine = new ConorSmith\Hoarde\Infra\TemplateEngine;

$router = new League\Route\Router;

/**
 * ROUTES
 */

$router->get("/", new ConorSmith\Hoarde\Infra\Controller\ShowLandingPage($templateEngine));

$router->post("/", new ConorSmith\Hoarde\Infra\Controller\GameBegins(
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

$router->get("/{gameId}/{locationId}", new ConorSmith\Hoarde\Infra\Controller\PlayerViewsLocation(
    new ConorSmith\Hoarde\UseCase\PlayerViewsLocation\UseCase(
        $gameRepository,
        $locationRepository,
        $entityRepository,
        $varietyRepository,
        $actionRepository
    ),
    $sessionSegment,
    $templateEngine,
    new ConorSmith\Hoarde\Infra\Presentation\EntityFactory(
        $varietyRepository,
        $resourceRepository
    ),
    new ConorSmith\Hoarde\Infra\Presentation\BlueprintFactory(
        $varietyRepository
    )
));

$router->get("/{gameId}", new ConorSmith\Hoarde\Infra\Controller\PlayerViewsGame(
    $locationRepository,
    $templateEngine
));

$router->post("/{gameId}/restart", new ConorSmith\Hoarde\Infra\Controller\GameRestarts(
    new \ConorSmith\Hoarde\UseCase\GameRestarts\UseCase(
        $gameRepository,
        $entityRepository,
        $locationRepository,
        $varietyRepository,
        $resourceRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{actorId}/travel", new ConorSmith\Hoarde\Infra\Controller\EntityTravels(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityTravels\UseCase(
        $gameRepository,
        $entityRepository,
        $locationRepository,
        $locationTemplateRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->get("/{gameId}/{actorId}/travel/{locationId}", new ConorSmith\Hoarde\Infra\Controller\EntityTravelsToLocation(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityTravelsToLocation\UseCase(
        $gameRepository,
        $entityRepository,
        $locationRepository,
        $locationTemplateRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{entityId}/wait", new ConorSmith\Hoarde\Infra\Controller\EntityWaits(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityWaits\UseCase(
        $gameRepository,
        $entityRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{entityId}/use", new ConorSmith\Hoarde\Infra\Controller\EntityUsesItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityUsesItem\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/consume", new ConorSmith\Hoarde\Infra\Controller\EntityConsumesResourceItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityConsumesResourceItem\UseCase(
        $entityRepository,
        $resourceRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{entityId}/scavenge", new ConorSmith\Hoarde\Infra\Controller\EntityScavenges(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityScavenges\UseCase(
        $gameRepository,
        $entityRepository,
        $locationRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/scavenge/{haulId}", new ConorSmith\Hoarde\Infra\Controller\EntityAddsScavengingHaul(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityAddsScavengingHaul\UseCase(
        $entityRepository,
        $scavengingHaulRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/drop", new ConorSmith\Hoarde\Infra\Controller\EntityDiscardsItem(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityDiscardsItem\UseCase(
        $entityRepository,
        $varietyRepository
    ),
    $findActorLocation
));

$router->post("/{gameId}/{entityId}/discard-from-incubator", new ConorSmith\Hoarde\Infra\Controller\EntityDiscardsFromIncubator(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityDiscardsFromIncubator\UseCase(
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/transfer", new ConorSmith\Hoarde\Infra\Controller\EntitiesTransferItems(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntitiesTransferItems\UseCase(
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    )
));

$router->post("/{gameId}/{entityId}/fetch-water", new ConorSmith\Hoarde\Infra\Controller\EntityFetchesWater(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityFetchesWater\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/construct", new ConorSmith\Hoarde\Infra\Controller\EntityBeginsConstructingEntity(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityBeginsConstructingEntity\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/repair/{targetId}", new ConorSmith\Hoarde\Infra\Controller\EntityBeginsRepairingEntity(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityBeginsRepairingEntity\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/construct/{targetId}", new ConorSmith\Hoarde\Infra\Controller\EntityContinuesConstructingEntity(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityContinuesConstructingEntity\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/sow/{targetId}", new ConorSmith\Hoarde\Infra\Controller\EntitySowsPlot(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntitySowsPlot\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/harvest-food/{targetId}", new ConorSmith\Hoarde\Infra\Controller\EntityHarvestsFoodFromPlot(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityHarvestsFoodFromPlot\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/harvest-seeds/{targetId}", new ConorSmith\Hoarde\Infra\Controller\EntityHarvestsSeedsFromPlot(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\EntityHarvestsSeedsFromPlot\UseCase(
        $gameRepository,
        $entityRepository,
        $varietyRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{entityId}/settings", new ConorSmith\Hoarde\Infra\Controller\PlayerRelabelsEntity(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\PlayerRelabelsEntity\UseCase(
        $entityRepository
    ),
    $findActorLocation
));

$router->post("/{gameId}/sort", new ConorSmith\Hoarde\Infra\Controller\PlayerSortsEntities(
    $sessionSegment,
    new ConorSmith\Hoarde\UseCase\PlayerSortsEntities\UseCase(
        $entityRepository,
        $unitOfWorkProcessor
    ),
    $findActorLocation
));

$router->post("/{gameId}/{actorId}/read/{varietyId}", new ConorSmith\Hoarde\Infra\Controller\PlayerReadsWrittenWork(
    new ConorSmith\Hoarde\UseCase\PlayerReadsWrittenWork\UseCase(
        $entityRepository,
        new ConorSmith\Hoarde\Infra\Repository\WrittenWorkRepositoryConfig($varietyRepository)
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
