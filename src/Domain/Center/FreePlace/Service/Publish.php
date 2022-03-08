<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\Applicant\Repository\ApplicantReadRepository;
use App\Domain\FreePlace\Log\Repository\LogCreatorRepository;
use App\Domain\FreePlace\Repository\FreePlaceUpdaterRepository;
use App\Domain\Ranging\Repository\RangingCreatorRepository;
use App\Domain\Company\Repository\RangingDeleterRepository;
use App\Domain\Applicant\Repository\ApplicantUpdaterRepository;
use DomainException;
use Predis\ClientInterface;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Publish extends Admin {

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var ApplicantReadRepository
     */
    private $applicantReadRepository;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * @var int
     */
    private $adminTypeId = 1;

    /**
     * @var int
     */
    private $statusId = 5;

    /**
     * @var LogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var FreePlaceUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var RangingCreatorRepository
     */
    private $createRepository;

    /**
     * @var RangingDeleterRepository
     */
    private $deleteRepository;

    /**
     * @var ApplicantUpdaterRepository
     */
    private $applicantUpdateRepository;

    /**
     * The constructor.
     * @param FreePlaceReadRepository       $readRepository
     * @param ApplicantReadRepository       $applicantReadRepository
     * @param LogCreatorRepository          $logCreateRepository
     * @param FreePlaceUpdaterRepository    $updateRepository
     * @param RangingCreatorRepository      $createRepository
     * @param RangingDeleterRepository      $deleteRepository
     * @param ApplicantUpdaterRepository    $applicantUpdateRepository
     * @param ClientInterface               $redis
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository,
                                ApplicantReadRepository $applicantReadRepository,
                                LogCreatorRepository $logCreateRepository,
                                FreePlaceUpdaterRepository $updateRepository,
                                RangingCreatorRepository $createRepository,
                                RangingDeleterRepository $deleteRepository,
                                ApplicantUpdaterRepository $applicantUpdateRepository,
                                ClientInterface $redis) {
        $this->readRepository = $readRepository;
        $this->applicantReadRepository = $applicantReadRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->updateRepository = $updateRepository;
        $this->createRepository = $createRepository;
        $this->deleteRepository = $deleteRepository;
        $this->applicantUpdateRepository = $applicantUpdateRepository;
        $this->redis = $redis;
    }

    /**
     * Publish to free places
     *
     * @param int $id The id of free place
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return array<mixed>
     */
    public function publish(int $id, array $post) {
        $hash = $post["hash"]?:"";
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
        $freePlaceInfo = $this->readRepository->findById($id);
        if(empty($freePlaceInfo)) {
            throw new DomainException("Free place not found");
        }
        if($freePlaceInfo["status_id"] != 3) {
            throw new DomainException("Free place status must be accepted");
        }
        $data = $this->redis->get($hash);
        if($data) {
            $data = json_decode($data, true);
            if($data["free_place_id"] != $id) {
                throw new DomainException("Data is incorrect");
            }
            $candidatesIds = (array)$data["candidates_ids"];
            $candidates = $this->applicantReadRepository->getCandidatesByIds($candidatesIds);
            $sign_arr = array(
                "Candidates" => [
                    "Candidate" => $this->getCandidatesForSign($candidates)
                ],
                "Signer iin" => [
                    "integer" => $this->getIin()
                ],
                "Sign date" => [
                    "datetime" => date("Y-m-d H:i:s")
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)){
                $log = array(
                    "free_place_id" => $id,
                    "admin_type_id" => $this->adminTypeId,
                    "admin_id" => $this->getAdminId(),
                    "company_bin" => $this->getOrgBin(),
                    "admin_full_name" => $certInfo["full_name"],
                    "status_id" => $this->statusId,
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    throw new DomainException("Free place not published");
                }
                $rangingDatas = $this->getRangingDatas($id, $candidates);
                $updateApplicantIds = array();
                foreach($rangingDatas as $ranging) {
                    if($this->createRepository->insert($ranging) == 0) {
                        $this->deleteRepository->deleteByFreePlaceId($id);
                        throw new DomainException("Free place not published");
                    }
                    $updateApplicantIds[] = $ranging["applicant_id"];
                }
                $this->applicantUpdateRepository->updateByIdsRanging($updateApplicantIds);
                $this->updateRepository->updateById($id, array('status_id' => $this->statusId));
            } else {
                throw new DomainException("Error to sign action");
            }
        } else { 
            throw new DomainException("Hash not found");
        }
    }

    /**
     * Get data for sign
     * 
     * @param array $candidates
     * 
     * @return array<mixed>
     */
    private function getCandidatesForSign(array $candidates) :array {
        $data = array();
        foreach ($candidates as $candidate) {
            $data[] = array(
                "id" => $candidate["id"],
                "raiting_number" => $candidate["raiting_number"],
                "iin" => $candidate["iin"],
                "full_name" => $candidate["full_name"],
                "privilege_id" => $candidate["privilege_id"],
                "positions" => $candidate["positions"]
            );
        }
        return $data;
    }

    /**
     * Get data for insert to db to table ranging
     * 
     * @param int $free_place_id
     * @param array<mixed> $applicants
     * 
     * @return array<mixed>
     */
    private function getRangingDatas(int $free_place_id, array $applicants) :array {
        foreach ($applicants as $i => $applicant) {
            $applicants[$i]["free_place_id"] = $free_place_id;
            $applicants[$i]["applicant_id"] = $applicant["id"];
            unset($applicants[$i]["id"]);
            //TODO: generate whatsapp url!
        }
        return $applicants;
    }
}