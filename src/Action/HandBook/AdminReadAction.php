<?php

namespace App\Action\HandBook;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Admin\Service\Admin;

/**
 * Action.
 */
final class AdminReadAction
{
    /**
     * @var Admin
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param Admin $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Admin $service, Responder $responder)
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
        $data = $this->service->get($args["lang"]);
        return $this->responder->success($response, null, $data);
    }
}
