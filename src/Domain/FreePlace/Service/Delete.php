<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\FreePlace\Repository\FreePlaceDeleterRepository;
use DomainException;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Delete extends Admin {

    /**
     * @var FreePlaceDeleterRepository
     */
    private $deleteRepository;

    /**
     * The constructor.
     * @param FreePlaceDeleterRepository $deleteRepository
     *
     */
    public function __construct(FreePlaceDeleterRepository $deleteRepository) {
        $this->deleteRepository = $deleteRepository;
    }

    /**
     * Delete 
     *
     * @param int $id Free place id
     * 
     */
    public function delete(int $id) {
        if($this->deleteRepository->deleteByBinAndId($this->getBin(), $id) == 0) {
            throw new DomainException("Error to delete free place");
        }
    }
}