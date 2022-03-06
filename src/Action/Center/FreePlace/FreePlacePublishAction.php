<?php

namespace App\Action\Center\FreePlace;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Center\FreePlace\Service\Publish;
use App\Middleware\CenterAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class FreePlacePublishAction{
    /**
     * @var Publish
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
     * @param Publish $service The service
     * @param Responder $responder The responder
     * @param Language $language The language
     */
    public function __construct(Publish $service, Responder $responder, Language $language) {
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
        $this->service->init($request->getAttribute(CenterAdminMiddleware::class));
        $post = (array)$request->getParsedBody();
        $data = $this->service->publish((int)$args["id"], $post);
        return $this->responder->success($response, $this->language->get("success")["Free place success published"]);
    }
}
