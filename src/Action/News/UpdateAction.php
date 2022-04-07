<?php

namespace App\Action\News;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\News\Service\Update as Service;
use App\Middleware\CenterAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class UpdateAction {

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * @var Language
     */
    private $language;

    /**
     * The constructor.
     *
     * @param Service $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Service $service, Responder $responder) {
        $this->service = $service;
        $this->responder = $responder;
        $this->language = new Language();
    }

    /**
     * Action.
     *
     * @param ServerRequestInterface $request The request
     * @param ResponseInterface $response The response
     * @param array<mixed> $args The arguments
     *
     * @return ResponseInterface The response
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface{
        $data = $request->getParsedBody();
        $this->service->init($request->getAttribute(CenterAdminMiddleware::class));
        $this->service->update((int) $args["news_id"], $data);
        return $this->responder->success($response, $this->language->get("success")["News updated successfully"]);
    }
}
