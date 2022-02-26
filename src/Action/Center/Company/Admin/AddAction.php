<?php

namespace App\Action\Center\Company\Admin;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Admin\Company\Service\Add;
use App\Middleware\CenterAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class AddAction{
    /**
     * @var Add
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
     * @param Add $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Add $service, Responder $responder, Language $language){
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
        $this->service->add($args["bin"], $post);

        return $this->responder->success($response, $this->language->get("success")["Company admin success added"]);
    }
}
