<?php

namespace App\Domain\News\Service;

use App\Domain\Company\Admin;
use App\Domain\News\Repository\NewsCreaterRepository as CreateRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Add extends Admin{

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
     *
     */
    public function __construct(CreateRepository $createRepository) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
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
        $data["bin"] = $this->getBin();
        // $id = $this->createRepository->insert($data);
        // if($id == 0) {
        //     throw new DomainException("Error to add free place");
        // }
    }
}