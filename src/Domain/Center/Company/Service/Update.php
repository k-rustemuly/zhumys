<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyUpdaterRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Update{

    /**
     * @var CompanyUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     *
     */
    public function __construct(CompanyUpdaterRepository $updateRepository){
        $this->updateRepository = $updateRepository;
        $this->validator = new Validator();
    }

    /**
     * Update company info
     *
     * @param int $id Company id
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return boolean updated successfuly?
     */
    public function update(int $id, array $data) :bool{
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        return $this->updateRepository->updateById($id, $data) > 0;
    }
}