<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Domain\LocationRepository;
use ConorSmith\Hoarde\Infra\TemplateEngine;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ramsey\Uuid\Uuid;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\RedirectResponse;

final class PlayerViewsGame
{
    /** @var LocationRepository */
    private $locationRepository;

    /** @var TemplateEngine */
    private $templateEngine;

    public function __construct(LocationRepository $locationRepository, TemplateEngine $templateEngine)
    {
        $this->locationRepository = $locationRepository;
        $this->templateEngine = $templateEngine;
    }

    public function __invoke(ServerRequestInterface $request, array $args): ResponseInterface
    {
        $gameId = Uuid::fromString($args['gameId']);

        $location = $this->locationRepository->findOrigin($gameId);

        if (is_null($location)) {
            return new HtmlResponse(
                $this->templateEngine->render("not-found.php"),
                404
            );
        }

        return new RedirectResponse("/{$gameId}/{$location->getId()}");
    }
}
