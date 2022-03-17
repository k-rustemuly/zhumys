<?php

namespace App\Action\News;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\News\Service\Add as Service;
use App\Helper\Language;
use App\Middleware\CenterAdminMiddleware;

/**
 * Action.
 */
final class AddAction {
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
     * @param Language $language The language
     */
    public function __construct(Service $service, Responder $responder, Language $language) {
        $this->service = $service;
        $this->responder = $responder;
        $this->language = $language;
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
        $this->language->locale($args['lang']);
        $post = (array)$request->getParsedBody();
        $this->service->init($request->getAttribute(CenterAdminMiddleware::class));
        $d = $this->service->add($post);

        return $this->responder->success($response, $this->language->get("success")["News success added"], $d);
    }
}
