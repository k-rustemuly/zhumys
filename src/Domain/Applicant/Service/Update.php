<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantUpdaterRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Update{

    /**
     * @var ApplicantUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param ApplicantUpdaterRepository $readRepository
     *
     */
    public function __construct(ApplicantUpdaterRepository $updateRepository){
        $this->updateRepository = $updateRepository;
        $this->validator = new Validator();
    }

    /**
     * Update Applicant info
     *
     * @param string $iin Applicant iin
     * @param array<mixed> $data The data
     *
     * @throws DomainException
     * 
     * @return boolean updated successfuly?
     */
    public function update(string $iin, array $data) :bool{
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        return $this->updateRepository->updateByIin($iin, $data) > 0;
    }
}