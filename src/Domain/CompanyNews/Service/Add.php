<?php

namespace App\Domain\CompanyNews\Service;

use App\Domain\CompanyNews\Repository\NewsFinderRepository as ReadRepository;
use App\Domain\Company\Admin;
use App\Helper\Language;
use App\Helper\File;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var ReadRepository
     */
    private $readRepository;

    /**
     * @var File
     */
    private $file;


    /**
     * The constructor.
     * @param ReadRepository $readRepository
     *
     */
    public function __construct(ReadRepository $readRepository, File $file) {
        $this->readRepository = $readRepository;
        $this->language = new Language();
        $this->file = $file;
    }

    /**
     * Add news
     * 
     * @param array<mixed> $post
     *
     * 
     */
    public function add(array $post = array()){
        //$data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $data = $post;
        //return $data;
        $to_insert = array();
        $to_insert["bin"] = $this->getBin();
        if(isset($data["image"])) {
            return $this->file->save($data["image"]);
        }
    }

}