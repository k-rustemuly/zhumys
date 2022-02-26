<?php

namespace App\Action\Applicant;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Applicant\Service\Update;
use App\Helper\Language;

/**
 * Action.
 */
final class ApplicantUpdateAction{
    /**
     * @var Update
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
     * @param Update $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Update $service, Responder $responder, Language $language){
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
        $this->service->update($args["iin"], $patch);

        return $this->responder->success($response, $this->language->get("success")["Applicant success updated"]);
    }
}
