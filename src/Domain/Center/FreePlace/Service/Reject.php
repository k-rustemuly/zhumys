<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\FreePlace\Repository\FreePlaceUpdaterRepository;
use App\Domain\FreePlace\Log\Repository\LogCreatorRepository;
use DomainException;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Reject extends Admin {
    /**
     * @var int
     */
    private $admin_type_id = 1;

    /**
     * @var int
     */
    private $status_id = 4;

    /**
     * @var LogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var FreePlaceUpdaterRepository
     */
    private $updateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param FreePlaceUpdaterRepository $updateRepository
     * @param LogCreatorRepository $logCreateRepository
     * @param Pki               $pki The pki client
     *
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository,
                                FreePlaceUpdaterRepository $updateRepository,
                                LogCreatorRepository $logCreateRepository,
                                Pki $pki) {
        $this->updateRepository = $updateRepository;
        $this->pki = $pki;
        $this->readRepository = $readRepository;
        $this->logCreateRepository = $logCreateRepository;
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param int $id The id of free place
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function reject(int $id, array $post) {
        $sign_p12 = $post["base64"]?:"";
        $password = $post["password"]?:"";
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);

        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        $reason = trim($post["reason"]);
        if(!isset($reason) || strlen($reason)<3) {
            throw new DomainException("The reason must be a string");
        }
        $freePlaceInfo = $this->readRepository->findById($id);
        if($freePlaceInfo["status_id"] != 2) {
            throw new DomainException("Free place status must be sended");
        }
        $sign_arr = array(
            "Id" => [
                "integer" => (int)$freePlaceInfo["id"]
            ],
            "Company bin" => [
                "integer" => $freePlaceInfo["bin"]
            ],
            "Signer iin" => [
                "integer" => $this->getIin()
            ],
            "Count" => [
                "integer" => $freePlaceInfo["count"]
            ],
            "Comment" => [
                "text" => $freePlaceInfo["comment"]
            ],
            "Current status id" => [
                "integer" => $freePlaceInfo["status_id"]
            ],
            "New status id" => [
                "integer" => $this->status_id
            ],
            "Reason" => [
                "text" => $reason
            ],
            "Created date" => [
                "datetime" => $freePlaceInfo["created_at"]
            ],
            "Updated date" => [
                "datetime" => $freePlaceInfo["updated_at"]
            ],
            "Sign date" => [
                "datetime" => date("Y-m-d H:i:s")
            ]
        );
        $xml = ArrayToXml::convert($sign_arr);
        $signed_result = $this->pki->sign($xml, $sign_p12, $password);
        if(!empty($signed_result)) {
            $log = array(
                "free_place_id" => $id,
                "admin_type_id" => $this->admin_type_id,
                "admin_id" => $this->getAdminId(),
                "admin_full_name" => $certInfo["full_name"],
                "status_id" => $this->status_id,
                "reason" => $reason,
                "company_bin" => $this->getOrgBin(),
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                throw new DomainException("Error to reject free place");
            }
            $this->updateRepository->updateById($id, array("status_id" => $this->status_id, "reason" => $reason));
        } else {
            throw new DomainException("Error to sign action");
        }
        
    }
}