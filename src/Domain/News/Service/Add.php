<?php

namespace App\Domain\News\Service;

use App\Domain\Company\Admin;
use App\Domain\News\Repository\NewsCreaterRepository as CreateRepository;
use DomainException;
use App\Helper\Validator;
use App\Helper\File;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var CreateRepository
     */
    private $createRepository;

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
        $this->validator = new Validator();
        $this->file = $file;
    }

    /**
     * 
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        //return $data;
        $to_insert = array();
        $to_insert["bin"] = $this->getBin();
        if(isset($data["bin"])) {
            return $this->file->save($data["image"]);
        }
        // $id = $this->createRepository->insert($data);
        // if($id == 0) {
        //     throw new DomainException("Error to add free place");
        // }
    }
}