<?php

namespace App\Action\Center\Company;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Center\Company\Service\Delete;
use App\Helper\Language;

/**
 * Action.
 */
final class CompanyDeleteAction
{
    /**
     * @var Delete
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
     * @param Delete $service The service
     * @param Responder $responder The responder
     */
    public function __construct(Delete $service, Responder $responder, Language $language)
    {
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
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, $args): ResponseInterface
    {
        $this->language->locale($args['lang']);
        //$data = $this->service->update($patch);
        return $this->responder->success($response, $this->language->get("success")["Company deleted"]);
    }
}
