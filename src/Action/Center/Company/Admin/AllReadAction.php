<?php

namespace App\Action\Center\Company\Admin;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Admin\Company\Service\AllRead;

/**
 * Action.
 */
final class AllReadAction{
    /**
     * @var Read
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param AllRead $service The service
     * @param Responder $responder The responder
     */
    public function __construct(AllRead $service, Responder $responder){
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
        return $this->responder->success($response, null, $this->service->list($args['lang']));
    }
}
