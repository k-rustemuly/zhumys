<?php

namespace App\Action\Applicant;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Applicant\Service\About;

/**
 * Action.
 */
final class ApplicantAboutAction{
    /**
     * @var About
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param About $service The service
     * @param Responder $responder The responder
     */
    public function __construct(About $service, Responder $responder){
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
        return $this->responder->success($response, null, $this->service->about((int) $args["id"], (string)$args['lang']));
    }
}
