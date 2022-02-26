<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Center\Admin;
use App\Domain\Company\Repository\CompanyReadRepository;
use App\Domain\Company\Repository\CompanyCreaterRepository;
use DomainException;
use App\Helper\StatGov;

/**
 * Service.
 */
final class Add extends Admin{

    /**
     * @var CompanyReadRepository
     */
    private $readRepository;

    /**
     * @var CompanyCreaterRepository
     */
    private $createRepository;

    /**
     * @var StatGov
     */
    private $stat;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     * @param CompanyCreaterRepository $createRepository
     * @param StatGov $stat
     *
     */
    public function __construct(CompanyReadRepository $readRepository,
                                CompanyCreaterRepository $createRepository,
                                StatGov $stat){
        $this->readRepository = $readRepository;
        $this->createRepository = $createRepository;
        $this->stat = $stat;
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post){
        if(!isset($post["bin"]) || strlen($post["bin"]) != 12) throw new DomainException("Bin is not presented");
        if(!isset($post["name_kk"])) throw new DomainException("Name of company on kazakh not presented");
        if(!isset($post["name_ru"])) throw new DomainException("Name of company on russia not presented");
        $bin = $post["bin"];
        $companyInfo = $this->readRepository->getByBin($bin);
        if(!empty($companyInfo)) throw new DomainException("Company is already presented"); 
        
        $companyInfo = $this->stat->getInfo($bin);
        if(empty($companyInfo)) throw new DomainException("Error to parse from stat gov api"); 
        $companyInfo["name_kk"] = $post["name_kk"];
        $companyInfo["name_ru"] = $post["name_ru"];
        if($this->createRepository->insert($companyInfo) == 0) throw new DomainException("Company not added"); 
    }
}