<?php
declare(strict_types=1);

namespace ConorSmith\Hoarde\Infra\Controller;

use ConorSmith\Hoarde\Infra\TemplateEngine;
use Psr\Http\Message\ResponseInterface;
use Zend\Diactoros\Response\HtmlResponse;

final class ShowNotFoundPage
{
    /** @var TemplateEngine */
    private $templateEngine;

    public function __construct(TemplateEngine $templateEngine)
    {
        $this->templateEngine = $templateEngine;
    }

    public function __invoke(): ResponseInterface
    {
        return new HtmlResponse(
            $this->templateEngine->render("not-found.php"),
            404
        );
    }
}
