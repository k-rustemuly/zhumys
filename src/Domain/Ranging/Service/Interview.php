<?php

namespace App\Domain\Ranging\Service;

use App\Domain\Ranging\Repository\RangingReaderRepository;
use App\Domain\Ranging\Repository\RangingUpdaterRepository;
use DomainException;
use App\Domain\Company\Admin;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;
use App\Domain\Ranging\Log\Repository\RangingLogCreatorRepository;
use App\Domain\Applicant\Repository\ApplicantUpdaterRepository;

/**
 * Service.
 */
final class Interview extends Admin{
    /**
     * @var int
     */
    private $status_id = 2;

    /**
     * @var array
     */
    private $info;

    /**
     * @var RangingReaderRepository
     */
    private $rangingReadRepository;

    /**
     * @var RangingLogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var RangingUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var ApplicantUpdaterRepository
     */
    private $applicantUpdateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     * @param RangingReaderRepository       $rangingReadRepository
     * @param RangingCreatorRepository      $rangingCreateRepository
     * @param RangingUpdaterRepository      $updateRepository
     * @param ApplicantUpdaterRepository    $applicantUpdateRepository
     * @param RangingReaderRepository       $rangingReadRepository
     * @param Pki                           $pki
     *
     */
    public function __construct(RangingReaderRepository $rangingReadRepository,
                                RangingLogCreatorRepository $logCreateRepository,
                                RangingUpdaterRepository $updateRepository,
                                ApplicantUpdaterRepository $applicantUpdateRepository,
                                Pki $pki) {
        $this->rangingReadRepository = $rangingReadRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->updateRepository = $updateRepository;
        $this->applicantUpdateRepository = $applicantUpdateRepository;
        $this->pki = $pki;
    }

    /**
     * Get about one free place
     * 
     * @param string $lang The interface language code
     * @param int $freePlaceId 
     * @param int $rangingId 
     * @param array<mixed> $post 
     * 
     * @throws DomainException
     * 
     */
    public function interview(string $lang, int $freePlaceId, int $rangingId, array $post) {
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $interview_date = $post["date"]?:"";
        $interview_time = $post["time"]?:"";
        $interview_comment = $post["comment"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if($certInfo["iin"] != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $this->info = $this->rangingReadRepository->findByIdAndFreePlaceIdAndBinAndLang($rangingId, $freePlaceId, $this->getBin(), $lang);
        if(empty($this->info)) {
            throw new DomainException("Ranging not found");
        }
        if($this->info["status_id"] != 1) {
            throw new DomainException("Ranging status must be on process");
        }
        $sign_arr = array(
            "Id" => [
                "integer" => (int)$this->info["id"]
            ],
            "Free place id" => [
                "integer" => $this->info["free_place_id"]
            ],
            "Applicant id" => [
                "integer" => $this->info["applicant_id"]
            ],
            "Signer iin" => [
                "integer" => $this->getIin()
            ],
            "Current status id" => [
                "integer" => $this->info["status_id"]
            ],
            "New status id" => [
                "integer" => $this->status_id
            ],
            "Interview date" => [
                "date" => $interview_date
            ],
            "Interview time" => [
                "time" => $interview_time
            ],
            "Interview comment" => [
                "text" => $interview_comment
            ],
            "Created date" => [
                "datetime" => $this->info["created_at"]
            ],
            "Updated date" => [
                "datetime" => $this->info["updated_at"]
            ],
            "Sign date" => [
                "datetime" => date("Y-m-d H:i:s")
            ]
        );
        $xml = ArrayToXml::convert($sign_arr);
        $signed_result = $this->pki->sign($xml, $sign_p12, $password);
        if(!empty($signed_result)){
            $log = array(
                "ranging_id" => $rangingId,
                "admin_id" => $this->getAdminId(),
                "admin_full_name" => $certInfo["full_name"],
                "status_id" => $this->status_id,
                "company_bin" => $this->getBin(),
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                throw new DomainException("Error to add interview");
            }
            $rangingUpdated = $this->updateRepository->updateById($rangingId, array("status_id" => $this->status_id, "interview_date" => $interview_date, "interview_time" => $interview_time, "interview_comment" => $interview_comment)) > 0;
            if($rangingUpdated) {
                $this->applicantUpdateRepository->updateByIdInterview((int)$this->info["applicant_id"]);
            }
        } else {
            throw new DomainException("Error to sign action");
        }
    }

}