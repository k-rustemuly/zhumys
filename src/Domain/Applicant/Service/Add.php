<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantCreatorRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Add{

    /**
     * @var ApplicantCreatorRepository
     */
    private $createRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param ApplicantCreatorRepository $createRepository
     *
     */
    public function __construct(ApplicantCreatorRepository $createRepository){
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
    }

    /**
     * Add new applicant
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post){
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $this->createRepository->insert($data); 
    }
}