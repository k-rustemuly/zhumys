<?php

namespace App\Action\Center\Company;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Center\Company\Service\Update as Service;
use App\Middleware\CenterAdminMiddleware;
use App\Helper\Language;

/**
 * Action.
 */
final class CompanyUpdateAction {
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
        $patch = (array)$request->getParsedBody();
        $this->service->init($request->getAttribute(CenterAdminMiddleware::class));
        $this->service->update($args["bin"], $patch);
        return $this->responder->success($response, $this->language->get("success")["Company info updated"]);
    }
}
