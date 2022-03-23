<?php

namespace App\Domain\CompanyInfo\Service;

use App\Domain\Company\Repository\CompanyUpdaterRepository as UpdateRepository;
use App\Domain\Company\Admin;

/**
 * Service.
 */
final class Update extends Admin {

    /**
     * @var UpdateRepository
     */
    private $updateRepository;

    /**
     * The constructor.
     * @param UpdateRepository $updateRepository
     *
     */
    public function __construct(UpdateRepository $updateRepository) {
        $this->updateRepository = $updateRepository;
    }

    /**
     * Get info
     * 
     * @param array<mixed>
     * 
     */
    public function update(array $data) {
        $update = array();
        if(isset($data["full_name_kk"])) {
            $update["full_name_kk"] = $data["full_name_kk"];
        }
        if(isset($data["full_name_ru"])) {
            $update["full_name_ru"] = $data["full_name_ru"];
        }
        if(isset($data["full_address_kk"])) {
            $update["full_address_kk"] = $data["full_address_kk"];
        }
        if(isset($data["full_address_ru"])) {
            $update["full_address_ru"] = $data["full_address_ru"];
        }
        if(isset($data["director_fullname"])) {
            $update["director_fullname"] = $data["director_fullname"];
        }
        if(isset($data["phone_number"])) {
            $update["phone_number"] = $data["phone_number"];
        }
        $this->updateRepository->updateByBin($this->getBin(), $update);
    }

}