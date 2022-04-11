<?php

namespace App\Domain\Applicant\Service;

use App\Domain\Applicant\Repository\ApplicantArchiveCreatorRepository;
use App\Domain\Applicant\Repository\ApplicantReadRepository;
use App\Domain\Applicant\Repository\ApplicantDeleterRepository;
use DomainException;
use App\Helper\Validator;
use App\Domain\Center\Admin;
use App\Domain\Applicant\Log\Repository\ApplicantLogCreatorRepository;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;
use App\Helper\File;

/**
 * Service.
 */
final class Archive extends Admin {

    /**
     * @var ApplicantArchiveCreatorRepository
     */
    private $createRepository;

    /**
     * @var ApplicantDeleterRepository
     */
    private $deleteRepository;

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
     * @var File
     */
    private $file;

    /**
     * The constructor.
     * @param ApplicantArchiveCreatorRepository $createRepository
     * @param ApplicantLogCreatorRepository $logCreateRepository
     * @param ApplicantReadRepository $readRepository
     * @param Pki $pki
     * @param File $file
     * @param ApplicantDeleterRepository $deleteRepository
     *
     */
    public function __construct(ApplicantArchiveCreatorRepository $createRepository,
                                ApplicantLogCreatorRepository $logCreateRepository,
                                ApplicantReadRepository $readRepository,
                                Pki $pki,
                                File $file,
                                ApplicantDeleterRepository $deleteRepository) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
        $this->logCreateRepository = $logCreateRepository;
        $this->readRepository = $readRepository;
        $this->pki = $pki;
        $this->file = $file;
        $this->deleteRepository = $deleteRepository;
    }

    /**
     * Add new applicant
     * 
     * @param integer $id ID applicant
     * @param array<mixed> $post fileds The post fields
     * 
     * @throws DomainException
     */
    public function archive(int $id, array $post) {
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);

        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $applicantInfo = $this->readRepository->findById($id);
        if($applicantInfo["status_id"] != 1) throw new DomainException("Applicant not archived");
        $data = array();
        $data["reason_type_id"] = isset($post["reason_type_id"]) && $post["reason_type_id"] > 0 ? $post["reason_type_id"] : 0;
        $data["reason"] = isset($post["reason"]) ? trim($post["reason"]) : null;
        if(isset($post["attachment"])) {
            $attachment = $this->file->save($post["attachment"]);
            if(!$attachment) throw new DomainException("File not saved");
            $data["attachment"] = "/".$attachment["dir"];
        }
        $data["applicant_id"] = $applicantInfo["id"];
        $data["raiting_number"] = $applicantInfo["raiting_number"];
        $data["status_id"] = $applicantInfo["status_id"];
        $data["iin"] = $applicantInfo["iin"];
        $data["full_name"] = $applicantInfo["full_name"];
        $data["birthdate"] = $applicantInfo["birthdate"];
        $data["privilege_id"] = $applicantInfo["privilege_id"];
        $data["positions"] = $applicantInfo["positions"];
        $data["email"] = $applicantInfo["email"];
        $data["phone_number"] = $applicantInfo["phone_number"];
        $data["address"] = $applicantInfo["address"];
        $data["second_phone_number"] = $applicantInfo["second_phone_number"];
        $data["is_have_whatsapp"] = $applicantInfo["is_have_whatsapp"];
        $data["is_have_telegram"] = $applicantInfo["is_have_telegram"];
        $data["comment"] = $applicantInfo["comment"];
        $data["last_visit"] = $applicantInfo["last_visit"];

        $archiveId = $this->createRepository->insert($data);
        if($archiveId > 0) {
            $sign_arr = array(
                "Applicant ID" => [
                    "integer" => $applicantInfo["id"]
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
                "Reason type id" => [
                    "integer" => $applicantInfo["reason_type_id"]
                ],
                "reason" => [
                    "text" => $applicantInfo["reason"]
                ],
                "Created date" => [
                    "datetime" => $applicantInfo["created_at"]
                ],
                "Updated date" => [
                    "datetime" => $applicantInfo["updated_at"]
                ],
                "Sign date" => [
                    "datetime" => date("Y-m-d H:i:s")
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)){
                $log = array(
                    "center_admins_id" => $this->getAdminId(),
                    "center_admin_full_name" => $certInfo["full_name"],
                    "applicant_id" => $applicantInfo["id"],
                    "applicant_full_name" => $applicantInfo["full_name"],
                    "action" => "archive",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    throw new DomainException("Applicant archive log not added");
                }
            } else {
                throw new DomainException("Error to sign action");
            }
        } else {
            throw new DomainException("Applicant archive not added");
        }
        $this->deleteRepository->deleteById($id);
    }
}