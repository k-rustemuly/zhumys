<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyUpdaterRepository;
use DomainException;
use App\Helper\Validator;
use App\Domain\Center\Admin;
use App\Domain\Company\Log\Repository\CompanyLogCreatorRepository;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Service.
 */
final class Update extends Admin {

    /**
     * @var CompanyReadRepository
     */
    private $readRepository;

    /**
     * @var CompanyUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var CompanyLogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     * @param CompanyReadRepository $readRepository
     * @param CompanyLogCreatorRepository $logCreateRepository
     * @param Pki $pki
     *
     */
    public function __construct(CompanyReadRepository $readRepository,
                                CompanyUpdaterRepository $updateRepository,
                                CompanyLogCreatorRepository $logCreateRepository,
                                Pki $pki) {
        $this->readRepository = $readRepository;
        $this->updateRepository = $updateRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->pki = $pki;
        $this->validator = new Validator();
    }

    /**
     * Update company info
     *
     * @param string $bin Company bin
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     */
    public function update(string $bin, array $data) {
        $sign_p12 = $data["base64"]?:"";
        $password = $data["password"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if(!$certInfo["is_individual"]) {
            throw new DomainException("Only individual usage digital signature accessed");
        }
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        $oldCompanyInfo = $this->readRepository->getByBin($bin);
        if(empty($oldCompanyInfo)) {
            throw new DomainException("Company not found");
        } 
        $oldData = $oldCompanyInfo;
        unset($oldData["id"]);
        unset($oldData["bin"]);
        unset($oldData["created_at"]);
        unset($oldData["updated_at"]);
        if($this->updateRepository->updateByBin($bin, $data) > 0) {
            $newCompanyInfo = $this->readRepository->getByBin($bin);
            $sign_arr = array(
                "Old company info" => $oldCompanyInfo,
                "New company info" => $newCompanyInfo 
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)) {
                $log = array(
                    "company_id" => $oldCompanyInfo["id"],
                    "admin_id" => $this->getAdminId(),
                    "admin_full_name" => $certInfo["full_name"],
                    "action" => "update",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    $this->updateRepository->updateByBin($bin, $oldData);
                    throw new DomainException("Not updated");
                }
            } else {
                $this->updateRepository->updateByBin($bin, $oldData);
                throw new DomainException("Not updated");
            }
        } else {
            throw new DomainException("Not updated");
        }
        
    }
}