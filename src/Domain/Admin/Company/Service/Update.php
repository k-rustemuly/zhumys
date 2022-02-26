<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Admin\Company\Repository\AdminUpdaterRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Update{

    /**
     * @var AdminUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param AdminUpdaterRepository $updateRepository
     *
     */
    public function __construct(AdminUpdaterRepository $updateRepository){
        $this->updateRepository = $updateRepository;
        $this->validator = new Validator();
    }

    /**
     * Update company admin info
     *
     * @param int $id The company admin id
     * @param array<mixed> $post The fields
     */
    public function update(string $id, array $data){
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        $this->updateRepository->updateById($id, $data); 
    }
}