<?php

namespace App\Action\Company\FreePlace;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\FreePlace\Service\Send as Service;
use App\Middleware\CompanyAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class SendAction {
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

        $this->service->init($request->getAttribute(CompanyAdminMiddleware::class));
        $post = (array)$request->getParsedBody();
        $this->service->send((int)$args["id"], $post);
        return $this->responder->success($response, $this->language->get("success")["Free place success sended"]);
    }
}
