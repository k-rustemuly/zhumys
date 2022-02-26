<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Center\Admin;
use App\Domain\Admin\Company\Repository\AdminCreaterRepository;
use DomainException;
use App\Helper\Validator;

/**
 * Service.
 */
final class Add extends Admin{

    /**
     * @var AdminCreaterRepository
     */
    private $createRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param AdminCreaterRepository $createRepository
     *
     */
    public function __construct(AdminCreaterRepository $createRepository){
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
    }

    /**
     * Add new company admin
     *
     * @param string $bin The company bin
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(string $bin, array $post){
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $data["org_bin"] = $bin;
        $data["added_center_admin_id"] = $this->getAdminId();
        if($this->createRepository->insert($data) == 0) throw new DomainException("Company admin not added"); 
    }
}