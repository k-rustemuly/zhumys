<?php

namespace App\Action\Center\Company\Admin;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Admin\Company\Service\Read;

/**
 * Action.
 */
final class ReadAction
{
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
     * @param Read $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Read $service, Responder $responder)
    {
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        return $this->responder->success($response, null, $this->service->list($args["bin"], $args['lang']));
    }
}
