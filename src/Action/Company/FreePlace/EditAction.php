<?php

namespace App\Action\Company\FreePlace;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\FreePlace\Service\Edit;
use App\Middleware\CompanyAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class EditAction{
    /**
     * @var Edit
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
     * @param Edit $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Edit $service, Responder $responder, Language $language){
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
        $patch = (array)$request->getParsedBody();
        $this->service->save((int)$args["id"], $patch);
        return $this->responder->success($response, $this->language->get("success")["Free place success edited"]);
    }
}
