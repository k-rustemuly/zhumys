<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Center\Admin;
use App\Domain\Company\Repository\CompanyReadRepository;
use App\Domain\Company\Repository\CompanyCreaterRepository;
use App\Domain\Company\Log\Repository\CompanyLogCreatorRepository;
use App\Domain\Company\Repository\CompanyDeleterRepository;
use DomainException;
use App\Helper\StatGov;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

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
     * @var CompanyLogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var CompanyDeleterRepository
     */
    private $deleteRepository;

    /**
     * @var StatGov
     */
    private $stat;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     * @param CompanyCreaterRepository $createRepository
     * @param CompanyLogCreatorRepository $logCreateRepository
     * @param CompanyDeleterRepository $deleteRepository
     * @param StatGov $stat
     * @param Pki $pki
     *
     */
    public function __construct(CompanyReadRepository $readRepository,
                                CompanyCreaterRepository $createRepository,
                                CompanyLogCreatorRepository $logCreateRepository,
                                CompanyDeleterRepository $deleteRepository,
                                StatGov $stat,
                                Pki $pki){
        $this->readRepository = $readRepository;
        $this->createRepository = $createRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->deleteRepository = $deleteRepository;
        $this->stat = $stat;
        $this->pki = $pki;
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post){
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        
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
        $companyId = $this->createRepository->insert($companyInfo);
        if($companyId == 0) throw new DomainException("Company not added"); 
        $companyInfo = $this->readRepository->getById($companyId);
        $sign_arr = array(
            "Id" => [
                "integer" => (int)$companyInfo["id"]
            ],
            "Company bin" => [
                "integer" => $companyInfo["bin"]
            ],
            "Signer iin" => [
                "integer" => $this->getIin()
            ],
            "Name kk" => [
                "text" => $companyInfo["name_kk"]
            ],
            "Name ru" => [
                "text" => $companyInfo["name_ru"]
            ],
            "Fullname kk" => [
                "text" => $companyInfo["full_name_kk"]
            ],
            "Fullname ru" => [
                "text" => $companyInfo["full_name_ru"]
            ],
            "Oked code" => [
                "integer" => $companyInfo["oked_code"]
            ],
            "Krp code" => [
                "integer" => $companyInfo["krp_code"]
            ],
            "Kato code" => [
                "integer" => $companyInfo["kato_code"]
            ],
            "Fulladdress kk" => [
                "text" => $companyInfo["full_address_kk"]
            ],
            "Fulladdress ru" => [
                "text" => $companyInfo["full_address_ru"]
            ],
            "Is ip" => [
                "boolean" => $companyInfo["is_ip"]
            ],
            "Director fullname" => [
                "text" => $companyInfo["director_fullname"]
            ],
            "Is active" => [
                "boolean" => $companyInfo["is_active"]
            ],
            "Created date" => [
                "datetime" => $companyInfo["created_at"]
            ],
            "Updated date" => [
                "datetime" => $companyInfo["updated_at"]
            ],
            "Sign date" => [
                "datetime" => date("Y-m-d H:i:s")
            ]
        );
        $xml = ArrayToXml::convert($sign_arr);
        $signed_result = $this->pki->sign($xml, $sign_p12, $password);
        if(!empty($signed_result)){
            $log = array(
                "company_id" => $companyId,
                "admin_id" => $this->getAdminId(),
                "admin_full_name" => $certInfo["full_name"],
                "action" => "add",
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                $this->deleteRepository->deleteById($companyId);
                throw new DomainException("Company not added");
            }
        } else {
            $this->deleteRepository->deleteById($companyId);
            throw new DomainException("Error to sign action");
        }
    }
}