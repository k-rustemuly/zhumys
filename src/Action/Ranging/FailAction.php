<?php

namespace App\Action\Ranging;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Ranging\Service\Fail as Service;
use App\Middleware\CompanyAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class FailAction {
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
        $this->service->init($request->getAttribute(CompanyAdminMiddleware::class));
        $post = (array)$request->getParsedBody();
        $this->service->fail((int)$args["id"], (int)$args['ranging_id'], $post);
        $this->language->locale($args["lang"]);
        return $this->responder->success($response, $this->language->get("success")["Ranging success rejected"]);
    }
}
