<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceUpdaterRepository;
use DomainException;
use App\Helper\Validator;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Edit extends Admin{

    /**
     * @var FreePlaceUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param FreePlaceUpdaterRepository $updateRepository
     *
     */
    public function __construct(FreePlaceUpdaterRepository $updateRepository){
        $this->updateRepository = $updateRepository;
        $this->validator = new Validator();
    }

    /**
     * Update 
     *
     * @param int $id Free place id
     * @param array<mixed> $data fileds to update
     * 
     */
    public function save(int $id, array $data) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        if($this->updateRepository->updateByBinAndId($this->getBin(), $id, $data) == 0) {
            throw new DomainException("Error to update free place");
        }
    }
}