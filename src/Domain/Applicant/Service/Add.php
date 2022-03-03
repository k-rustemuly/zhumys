<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantCreatorRepository;
use App\Domain\Applicant\Repository\ApplicantReadRepository;
use DomainException;
use App\Helper\Validator;
use App\Domain\Center\Admin;
use App\Domain\Applicant\Log\Repository\ApplicantLogCreatorRepository;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Add extends Admin {

    /**
     * @var ApplicantCreatorRepository
     */
    private $createRepository;

    /**
     * @var Validator
     */
    private $validator;

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
     * The constructor.
     * @param ApplicantCreatorRepository $createRepository
     * @param ApplicantLogCreatorRepository $logCreateRepository
     * @param ApplicantReadRepository $readRepository
     * @param Pki $pki
     *
     */
    public function __construct(ApplicantCreatorRepository $createRepository,
                                ApplicantLogCreatorRepository $logCreateRepository,
                                ApplicantReadRepository $readRepository,
                                Pki $pki) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
        $this->logCreateRepository = $logCreateRepository;
        $this->readRepository = $readRepository;
        $this->pki = $pki;
    }

    /**
     * Add new applicant
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
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
        $applicantId = $this->createRepository->insert($data);
        if($applicantId > 0) {
            $applicantInfo = $this->readRepository->findById($applicantId);
            $sign_arr = array(
                "Raiting number" => [
                    "integer" => $applicantInfo["raiting_number"]
                ],
                "Iin" => [
                    "integer" => $applicantInfo["iin"]
                ],
                "Signer iin" => [
                    "integer" => $this->getIin()
                ],
                "Fullname" => [
                    "text" => $applicantInfo["full_name"]
                ],
                "Birthdate" => [
                    "date" => $applicantInfo["birthdate"]
                ],
                "Email" => [
                    "text" => $applicantInfo["email"]
                ],
                "Phone number" => [
                    "text" => $applicantInfo["phone_number"]
                ],
                "Address" => [
                    "text" => $applicantInfo["address"]
                ],
                "Second phone number" => [
                    "text" => $applicantInfo["second_phone_number"]
                ],
                "Is have whatsapp" => [
                    "boolean" => $applicantInfo["is_have_whatsapp"]
                ],
                "Is have telegram" => [
                    "boolean" => $applicantInfo["is_have_telegram"]
                ],
                "comment" => [
                    "text" => $applicantInfo["comment"]
                ],
                "Created date" => [
                    "datetime" => $applicantInfo["created_at"]
                ],
                "Updated date" => [
                    "datetime" => $applicantInfo["updated_at"]
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)){
                $log = array(
                    "center_admins_id" => $this->getAdminId(),
                    "center_admin_full_name" => $certInfo["full_name"],
                    "applicant_id" => $applicantId,
                    "applicant_full_name" => $applicantInfo["full_name"],
                    "action" => "add",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    //TODO: delete the inserted record applicant info!
                    throw new DomainException("Applicant not added");
                }
            } else {
                //TODO: delete the inserted record applicant info!
                throw new DomainException("Error to sign action");
            }
        } else {
            throw new DomainException("Applicant not added");
        }
    }
}