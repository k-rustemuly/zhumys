<?php

namespace App\Domain\CompanyNews\Service;

use App\Domain\Company\Admin;
use App\Helper\Language;
use App\Helper\File;
use DomainException;
use App\Helper\Validator;
use App\Domain\News\Repository\NewsCreaterRepository as CreateRepository;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var CreateRepository
     */
    private $createRepository;

    /**
     * @var File
     */
    private $file;

    /**
     * @var Validator
     */
    private $validator;


    /**
     * The constructor.
     * @param CreateRepository $createRepository
     * @param File $file
     *
     */
    public function __construct(CreateRepository $createRepository, File $file) {
        $this->createRepository = $createRepository;
        $this->language = new Language();
        $this->file = $file;
        $this->validator = new Validator();
    }

    /**
     * Add news
     * 
     * @param array<mixed> $post
     *
     * 
     */
    public function add(array $post = array()){
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $to_insert = array();
        $to_insert["bin"] = $this->getBin();
        if(isset($data["image"])) {
            $image = $this->file->save($data["image"]);
            if(!$image) throw new DomainException("File not saved");
            $to_insert["image"] = "/".$image["dir"];
        }
        $to_insert["is_public"] = (bool) $data["is_public"];
        $to_insert["lang"] = $data["lang"];
        $to_insert["title"] = trim($data["title"]);
        $to_insert["anons"] = trim($data["anons"]);
        $to_insert["body"] = trim($data["body"]);
        $this->createRepository->insert($to_insert);
    }

}