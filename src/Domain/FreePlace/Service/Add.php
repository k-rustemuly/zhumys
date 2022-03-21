<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Company\Admin;
use App\Domain\FreePlace\Repository\FreePlaceCreaterRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var FreePlaceCreaterRepository
     */
    private $createRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param FreePlaceCreaterRepository $createRepository
     *
     */
    public function __construct(FreePlaceCreaterRepository $createRepository) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
    }

    /**
     * Sign in company admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $data["bin"] = $this->getBin();
        $id = $this->createRepository->insert($data);
        if($id == 0) {
            throw new DomainException("Error to add free place");
        }
    }
}