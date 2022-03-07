<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantUpdaterRepository;
use App\Domain\Applicant\Repository\ApplicantReadRepository;
use App\Domain\Applicant\Log\Repository\ApplicantLogCreatorRepository;
use App\Domain\Center\Admin;
use DomainException;
use App\Helper\Validator;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Update extends Admin {

    /**
     * @var ApplicantUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var ApplicantReadRepository
     */
    private $readRepository;

    /**
     * @var ApplicantLogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * The constructor.
     * @param ApplicantUpdaterRepository $updateRepository
     * @param ApplicantReadRepository $readRepository
     * @param ApplicantLogCreatorRepository $logCreateRepository
     * @param Pki $pki
     *
     */
    public function __construct(ApplicantUpdaterRepository $updateRepository,
                                ApplicantReadRepository $readRepository,
                                ApplicantLogCreatorRepository $logCreateRepository,
                                Pki $pki) {
        $this->updateRepository = $updateRepository;
        $this->readRepository = $readRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->pki = $pki;
        $this->validator = new Validator();
    }

    /**
     * Update Applicant info
     *
     * @param string $iin Applicant iin
     * @param array<mixed> $data The data
     *
     * @throws DomainException
     */
    public function update(string $iin, array $data){
        $sign_p12 = $data["base64"]?:"";
        $password = $data["password"]?:"";
        $data = $this->validator->setConfig(Read::getHeader())->validateOnUpdate($data);
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if(!$certInfo["is_individual"]) {
            throw new DomainException("Only individual usage digital signature accessed");
        }
        $center_admin_iin = (string)$certInfo["iin"];
        if($center_admin_iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $oldInfo = $this->readRepository->findByIin($iin);
        if(empty($oldInfo)) {
            throw new DomainException("Applicant not found");
        } 
        $oldData = $oldInfo;
        unset($oldData["id"]);
        unset($oldData["last_visit"]);
        unset($oldData["created_at"]);
        unset($oldData["updated_at"]);
        $data["positions"] = Add::parsePosition($data["positions"]);
        if($this->updateRepository->updateByIin($iin, $data) > 0 ) {
            $newInfo = $this->readRepository->findByIin($iin);
            $sign_arr = array(
                "Old info" => $oldInfo,
                "New info" => $newInfo 
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)) {
                $log = array(
                    "applicant_id" => $newInfo["id"],
                    "center_admins_id" => $this->getAdminId(),
                    "center_admin_full_name" => $certInfo["full_name"],
                    "applicant_full_name" => $newInfo["full_name"],
                    "action" => "update",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    $this->updateRepository->updateByIin($iin, $oldData);
                    throw new DomainException("Not updated");
                }
            } else {
                $this->updateRepository->updateByIin($iin, $oldData);
                throw new DomainException("Not updated");
            }
        } else {
            throw new DomainException("Not updated");
        }
    }
}