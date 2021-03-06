<?php

namespace App\Action\Sign\Company;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Sign\Company\Service\SignIn as Service;

/**
 * Action.
 */
final class SignInEcpAction {
    /**
     * @var Service
     */
    private $service;

    /**
     * @var Responder
     */
    private $responder;

    /**
     * The constructor.
     *
     * @param Service $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Service $service, Responder $responder) {
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
        $post = (array)$request->getParsedBody();
        $params = $request->getServerParams();
        $post["lang"] = $args['lang'];
        $post["user_agent"] = $params['HTTP_USER_AGENT'];
        $post["user_ip_address"] = $params['REMOTE_ADDR'];
        $data = $this->service->pkcs($post);

        return $this->responder->withJson($response, $data);
    }
}
