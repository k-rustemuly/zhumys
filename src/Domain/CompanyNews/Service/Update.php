<?php

namespace App\Domain\CompanyNews\Service;

use App\Domain\News\Repository\NewsUpdaterRepository as UpdateRepository;
use App\Domain\Company\Admin;
use App\Helper\File;
use DomainException;

/**
 * Service.
 */
final class Update extends Admin {

    /**
     * @var UpdateRepository
     */
    private $updateRepository;

    /**
     * @var File
     */
    private $file;


    /**
     * The constructor.
     * @param UpdateRepository $updateRepository
     *
     */
    public function __construct(UpdateRepository $updateRepository, File $file) {
        $this->updateRepository = $updateRepository;
        $this->file = $file;
    }

    /**
     * Update
     * 
     * @param array<mixed>
     * 
     */
    public function update(int $id, array $data) {
        $update = array();
        if(isset($data["is_public"])) {
            $update["is_public"] = (bool)$data["is_public"];
        }
        if(isset($data["image"])) {
            $image = $this->file->save($data["image"]);
            if(!$image) throw new DomainException("File not saved");
            $update["image"] = "/".$image["dir"];
        }
        if(isset($data["lang"])) {
            $update["lang"] = $data["lang"];
        }
        if(isset($data["title"])) {
            $update["title"] = $data["title"];
        }
        if(isset($data["anons"])) {
            $update["anons"] = $data["anons"];
        }
        if(isset($data["body"])) {
            $update["body"] = $data["body"];
        }
        $this->updateRepository->updateByBinAndId($this->getBin(), $id, $update);
    }

}