<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Center\Admin;
use App\Domain\Admin\Company\Repository\AdminCreaterRepository;
use App\Domain\Admin\Company\Repository\AdminReadRepository;
use App\Domain\Admin\Company\Log\Repository\CompanyAdminLogCreatorRepository;
use DomainException;
use App\Helper\Validator;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var AdminCreaterRepository
     */
    private $createRepository;

    /**
     * @var AdminReadRepository
     */
    private $readRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var CompanyAdminLogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     * @param AdminCreaterRepository $createRepository
     * @param CompanyAdminLogCreatorRepository $logCreateRepository
     * @param AdminReadRepository $readRepository
     * @param Pki $pki
     *
     */
    public function __construct(
                                AdminCreaterRepository $createRepository,
                                CompanyAdminLogCreatorRepository $logCreateRepository,
                                AdminReadRepository $readRepository,
                                Pki $pki) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
        $this->logCreateRepository = $logCreateRepository;
        $this->readRepository = $readRepository;
        $this->pki = $pki;
    }

    /**
     * Add new company admin
     *
     * @param string $bin The company bin
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(string $bin, array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $data["org_bin"] = $bin;
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if(!$certInfo["is_individual"]) {
            throw new DomainException("Only individual usage digital signature accessed");
        }
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $companyAdminId = $this->createRepository->insert($data);
        if($companyAdminId == 0) throw new DomainException("Company admin not added"); 
        $adminInfo = $this->readRepository->findById($companyAdminId);
        $sign_arr = array(
            "Iin" => [
                "integer" => $adminInfo["iin"]
            ],
            "Company bin" => [
                "integer" => $adminInfo["org_bin"]
            ],
            "Signer iin" => [
                "integer" => $this->getIin()
            ],
            "Fullname" => [
                "text" => $adminInfo["full_name"]
            ],
            "Birthdate" => [
                "date" => $adminInfo["birthdate"]
            ],
            "Email" => [
                "text" => $adminInfo["email"]
            ],
            "Is active" => [
                "boolean" => $adminInfo["is_active"]
            ],
            "Created date" => [
                "datetime" => $adminInfo["created_at"]
            ],
            "Updated date" => [
                "datetime" => $adminInfo["updated_at"]
            ],
            "Sign date" => [
                "datetime" => date("Y-m-d H:i:s")
            ]
        );
        $xml = ArrayToXml::convert($sign_arr);
        $signed_result = $this->pki->sign($xml, $sign_p12, $password);
        if(!empty($signed_result)){
            $log = array(
                "company_admins_id" => $companyAdminId,
                "center_admins_id" => $this->getAdminId(),
                "center_admin_full_name" => $certInfo["full_name"],
                "company_admin_full_name" => $adminInfo["full_name"],
                "action" => "add",
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                //TODO: Company admin delete!
                throw new DomainException("Company admin not added");
            }
        } else {
            //TODO: Company admin delete!
            throw new DomainException("Error to sign action");
        }
    }
}