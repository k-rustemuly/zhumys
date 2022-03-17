<?php

namespace App\Action\Center\FreePlace;

use App\Responder\Responder;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Domain\Center\FreePlace\Service\Read;
use App\Domain\Center\FreePlace\Service\Export;
use App\Helper\Language;

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
     * @var Language
     */
    private $language;

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
        $this->language = new Language();
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
        $this->language->get('export');
        $fileName = $this->language->getSub('Free-place');
        $data = $this->readService->list($args['lang'], $request->getQueryParams());
        $spreadsheet = $this->service->getSpreadsheet($data);
        return $this->responder->excel($response, $spreadsheet, $fileName);
    }
}
