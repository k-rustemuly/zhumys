<?php

namespace App\Action\News;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\News\Service\Read as Service;
use App\Middleware\CenterAdminMiddleware;

/**
 * Action.
 */
final class ReadAction{
    /**
     * @var Service
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param Service $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Service $service, Responder $responder) {
        $this->service = $service;
        $this->responder = $responder;
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
        $this->service->init($request->getAttribute(CenterAdminMiddleware::class));
        $params = $request->getQueryParams();
        $data = $this->service->list($args['lang'], $params);
        return $this->responder->success($response, null, $data);
    }
}
