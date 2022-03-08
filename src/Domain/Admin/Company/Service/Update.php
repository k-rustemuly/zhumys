<?php

namespace App\Domain\Admin\Company\Service;

use App\Domain\Admin\Company\Repository\AdminUpdaterRepository;
use App\Domain\Admin\Company\Repository\AdminReadRepository;
use App\Domain\Admin\Company\Log\Repository\CompanyAdminLogCreatorRepository;
use DomainException;
use App\Helper\Validator;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;
use App\Domain\Center\Admin;

/**
 * Service.
 */
final class Update extends Admin{
    /**
     * @var AdminReadRepository
     */
    private $readRepository;

    /**
     * @var AdminUpdaterRepository
     */
    private $updateRepository;

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
     * @param AdminUpdaterRepository $updateRepository
     * @param AdminReadRepository $readRepository
     * @param CompanyAdminLogCreatorRepository $logCreateRepository
     * @param Pki $pki
     *
     */
    public function __construct(AdminUpdaterRepository $updateRepository,
                                AdminReadRepository $readRepository,
                                CompanyAdminLogCreatorRepository $logCreateRepository,
                                Pki $pki) {
        $this->updateRepository = $updateRepository;
        $this->readRepository = $readRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->pki = $pki;
        $this->validator = new Validator();
    }

    /**
     * Update company admin info
     *
     * @param int $id The company admin id
     * @param array<mixed> $post The fields
     */
    public function update(int $id, array $data){
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
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
        $oldInfo = $this->readRepository->findById($id);
        if(empty($oldInfo)) {
            throw new DomainException("Company admin not found");
        }
        $oldData = $oldInfo;
        unset($oldData["id"]);
        unset($oldData["last_visit"]);
        unset($oldData["created_at"]);
        unset($oldData["updated_at"]);
        if($this->updateRepository->updateById($id, $data) > 0) {
            $newInfo = $this->readRepository->findById($id);
            $sign_arr = array(
                "Old info" => $oldInfo,
                "New info" => $newInfo,
                "Sign date" => [
                    "datetime" => date("Y-m-d H:i:s")
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)) {
                $log = array(
                    "company_admins_id" => $oldInfo["id"],
                    "center_admins_id" => $this->getAdminId(),
                    "center_admin_full_name" => $certInfo["full_name"],
                    "company_admin_full_name" => $newInfo["full_name"],
                    "action" => "update",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    $this->updateRepository->updateById($id, $oldData);
                    throw new DomainException("Not updated");
                }
            } else {
                $this->updateRepository->updateById($id, $oldData);
                throw new DomainException("Not updated");
            }
        }else {
            throw new DomainException("Not updated");
        }
    }
}