<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Company\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\FreePlace\Repository\FreePlaceUpdaterRepository;
use App\Domain\FreePlace\Log\Repository\LogCreatorRepository;
use DomainException;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Send extends Admin{
    /**
     * @var int
     */
    private $admin_type_id = 2;

    /**
     * @var int
     */
    private $status_id = 2;

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
     * Sign in company admin by digital signature.
     *
     * @param int $id The id of free place
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function send(int $id, array $post) {
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
        $bin = $this->getBin();
        $freePlaceInfo = $this->readRepository->findByBinAndId($bin, $id);
        if($freePlaceInfo["status_id"] != 1) {
            throw new DomainException("Free place status must be created");
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
                "text" => $freePlaceInfo["reason"]
            ],
            "Created date" => [
                "datetime" => $freePlaceInfo["created_at"]
            ],
            "Updated date" => [
                "datetime" => $freePlaceInfo["updated_at"]
            ]
        );
        $xml = ArrayToXml::convert($sign_arr);
        $signed_result = $this->pki->sign($xml, $sign_p12, $password);
        if(!empty($signed_result)){
            $log = array(
                "free_place_id" => $id,
                "admin_type_id" => $this->admin_type_id,
                "admin_id" => $this->getAdminId(),
                "admin_full_name" => $certInfo["surname"]." ".$certInfo["name"]." ".$certInfo["lastname"],
                "status_id" => $this->status_id,
                "field" => $signed_result["raw"],
                "sign" => $signed_result["xml"]
            );
            if($this->logCreateRepository->insert($log) == 0) {
                throw new DomainException("Error to add free place");
            }
            $this->updateRepository->updateByBinAndId($bin, $id, array("status_id" => $this->status_id));
        } else {
            throw new DomainException("Error to sign action");
        }
        
    }
}