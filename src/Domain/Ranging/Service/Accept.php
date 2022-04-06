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
use App\Domain\FreePlace\Repository\FreePlaceUpdaterRepository;
use App\Domain\FreePlace\Log\Repository\LogCreatorRepository;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\CompanyEmployee\Repository\CompanyEmployeeCreatorRepository;

/**
 * Service.
 */
final class Accept extends Admin {
    /**
     * @var int
     */
    private $status_id = 3;

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
     * @var LogCreatorRepository
     */
    private $freePlaceLogCreateRepository;

    /**
     * @var FreePlaceUpdaterRepository
     */
    private $freePlaceUpdateRepository;

    /**
     * @var FreePlaceReadRepository
     */
    private $freePlaceReadRepository;

    /**
     * @var CompanyEmployeeCreatorRepository
     */
    private $companyEmployeeCreatorRepository;

    /** 
     * The constructor.
     * @param RangingReaderRepository           $rangingReadRepository
     * @param RangingCreatorRepository          $rangingCreateRepository
     * @param RangingUpdaterRepository          $updateRepository
     * @param ApplicantUpdaterRepository        $applicantUpdateRepository
     * @param RangingReaderRepository           $rangingReadRepository
     * @param LogCreatorRepository              $freePlaceLogCreateRepository
     * @param FreePlaceUpdaterRepository        $freePlaceUpdateRepository
     * @param FreePlaceReadRepository           $freePlaceReadRepository
     * @param CompanyEmployeeCreatorRepository  $companyEmployeeCreatorRepository
     * @param Pki                               $pki
     *
     */
    public function __construct(RangingReaderRepository $rangingReadRepository,
                                RangingLogCreatorRepository $logCreateRepository,
                                RangingUpdaterRepository $updateRepository,
                                ApplicantUpdaterRepository $applicantUpdateRepository,
                                LogCreatorRepository $freePlaceLogCreateRepository,
                                FreePlaceUpdaterRepository $freePlaceUpdateRepository,
                                FreePlaceReadRepository $freePlaceReadRepository,
                                CompanyEmployeeCreatorRepository $companyEmployeeCreatorRepository,
                                Pki $pki) {
        $this->rangingReadRepository = $rangingReadRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->updateRepository = $updateRepository;
        $this->applicantUpdateRepository = $applicantUpdateRepository;
        $this->freePlaceLogCreateRepository = $freePlaceLogCreateRepository;
        $this->freePlaceUpdateRepository = $freePlaceUpdateRepository;
        $this->freePlaceReadRepository = $freePlaceReadRepository;
        $this->companyEmployeeCreatorRepository = $companyEmployeeCreatorRepository;
        $this->pki = $pki;
    }

    /**
     * reject the candidate
     * 
     * @param int $freePlaceId 
     * @param int $rangingId 
     * @param array<mixed> $post 
     * 
     * @throws DomainException
     * 
     */
    public function accept(int $freePlaceId, int $rangingId, array $post) {
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $reason = $post["reason"]?:"";
        $order_no = $post["order_no"]?:null;
        $order_date = $post["order_date"]?:null;
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if($certInfo["iin"] != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $this->info = $this->rangingReadRepository->findByIdAndFreePlaceIdAndBin($rangingId, $freePlaceId, $this->getBin());
        if(empty($this->info)) {
            throw new DomainException("Ranging not found");
        }
        if($this->info["status_id"] == 3 || $this->info["status_id"] == 4) {
            throw new DomainException("Ranging status must be on process or on interview");
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
            "Reason" => [
                "text" => $reason
            ],
            "Order number" => [
                "text" => $order_no
            ],
            "Order date" => [
                "date" => $order_date
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
        if(!empty($signed_result)) {
            $log = array(
                "ranging_id" => $rangingId,
                "admin_id" => $this->getAdminId(),
                "admin_full_name" => $certInfo["full_name"],
                "status_id" => $this->status_id,
                "company_bin" => $this->getBin(),
                "reason" => $reason,
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                throw new DomainException("Error to add interview");
            }
            $rangingUpdated = $this->updateRepository->updateById($rangingId, array("status_id" => $this->status_id, "reason" => $reason, "order_no" => $order_no, "order_date" => $order_date)) > 0;
            if($rangingUpdated) {
                if($this->applicantUpdateRepository->updateByIdAccept((int)$this->info["applicant_id"]) > 0) {
                    $this->freePlaceUpdateRepository->updateByBinAndId($this->getBin(), $freePlaceId, array("employed_count" => "+1"));
                    $this->companyEmployeeCreatorRepository->insert($this->mapToEmployeeData($order_no, $order_date));
                }
                $this->checkRanging($freePlaceId, $sign_p12, $password, $certInfo["full_name"]);
            }
        } else {
            throw new DomainException("Error to sign action");
        }
    }

    /**
     * Check ranging and close if all candidates processed
     * 
     * @param int $freePlaceId
     * @param string $sign_p12
     * @param string $password
     * @param string $admin_full_name
     * 
     */
    private function checkRanging(int $freePlaceId, string $sign_p12, string $password, string $admin_full_name) {
        $rangingInfo = $this->rangingReadRepository->getAllByFreePlaceId($freePlaceId);
        $close = true;
        foreach ($rangingInfo as $rangeInfo) {
            if($rangeInfo["status_id"] == 1 || $rangeInfo["status_id"] == 2) {
                $close = false;
                break;
            }
        }
        $freePlaceInfo = $this->freePlaceReadRepository->findById($freePlaceId);
        if($freePlaceInfo["count"] == $freePlaceInfo["employed_count"]) {
            $close = true;
        }
        if($close) {
            $sign_arr = array(
                "Id" => [
                    "integer" => $freePlaceId
                ],
                "Company bin" => [
                    "integer" => $this->getBin()
                ],
                "Signer iin" => [
                    "integer" => $this->getIin()
                ],
                "New status id" => [
                    "integer" => 6
                ],
                "Sign date" => [
                    "datetime" => date("Y-m-d H:i:s")
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)) {
                $log = array(
                    "free_place_id" => $freePlaceId,
                    "admin_type_id" => 2,
                    "admin_id" => $this->getAdminId(),
                    "admin_full_name" => $admin_full_name,
                    "status_id" => 6,
                    "company_bin" => $this->getBin(),
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->freePlaceLogCreateRepository->insert($log) == 0) {
                    throw new DomainException("Error to add free place log");
                }
                $this->freePlaceUpdateRepository->updateByBinAndId($this->getBin(), $freePlaceId, array("status_id" => 6));
            } else {
                throw new DomainException("Error to sign action");
            }
        }
    }
    
    /**
     * Map to insert data to company employees table
     * 
     * @param array<mixed>
     */
    private function mapToEmployeeData(?string $order_no = null, ?string $order_date = null) :array{
        return array(
            "bin" => $this->getBin(),
            "ranging_id" => $this->info["id"],
            "free_place_id" => $this->info["free_place_id"],
            "applicant_id" => $this->info["applicant_id"],
            "iin" => $this->info["iin"],
            "full_name" => $this->info["full_name"],
            "birthdate" => $this->info["birthdate"],
            "privilege_id" => $this->info["privilege_id"],
            "positions" => $this->info["positions"],
            "email" => $this->info["email"],
            "phone_number" => $this->info["phone_number"],
            "address" => $this->info["address"],
            "second_phone_number" => $this->info["second_phone_number"],
            "comment" => $this->info["comment"],
            "order_no" => $order_no,
            "order_date" => $order_date
        );
    }
}