<?php

namespace App\Domain\News\Service;

use App\Domain\Center\Admin;
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
        $to_insert = array();
        $to_insert["bin"] = $this->getOrgBin();
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