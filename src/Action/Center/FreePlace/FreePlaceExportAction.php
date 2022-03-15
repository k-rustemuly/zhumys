<?php

namespace App\Action\Center\FreePlace;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Center\FreePlace\Service\Read;
use App\Domain\Center\FreePlace\Service\Export;

/**
 * Action.
 */
final class FreePlaceExportAction{
    /**
     * @var Read
     */
    private $readService;

    /**
     * @var Export
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
    public function __construct(Export $service, Read $readService, Responder $responder){
        $this->service = $service;
        $this->readService = $readService;
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
        $data = $this->readService->list($args['lang'], $request->getQueryParams());
        $spreadsheet = $this->service->getSpreadsheet($args['lang'], $data);
        return $this->responder->excel($response, $spreadsheet);
    }
}
