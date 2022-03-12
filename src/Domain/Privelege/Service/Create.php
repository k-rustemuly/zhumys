<?php

namespace App\Domain\Privelege\Service;

use DomainException;
use App\Helper\Validator;
use App\Domain\Center\Admin;
use App\Domain\Privelege\Repository\PrivilegeCreateRepository;
use App\Domain\ReferenceLog\Repository\ReferenceLogCreateRepository;
use App\Helper\Pki;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Create extends Admin {

    /**
     * @var PositionCreateRepository
     */
    private $createRepository;

    /**
     * @var Validator
     */
    private $validator;

    /**
     * @var ReferenceLogCreateRepository
     */
    private $logCreateRepository;

    /**
     * @var Pki
     */
    private $pki;

    /**
     * @var string
     */
    private $reference_name = "rb_privileges";

    /**
     * The constructor.
     * @param PrivilegeCreateRepository $createRepository
     * @param ReferenceLogCreateRepository $logCreateRepository
     * @param Pki $pki
     *
     */
    public function __construct(PrivilegeCreateRepository $createRepository,
                                ReferenceLogCreateRepository $logCreateRepository,
                                Pki $pki) {
        $this->createRepository = $createRepository;
        $this->validator = new Validator();
        $this->logCreateRepository = $logCreateRepository;
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
        
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }

        $id = $this->createRepository->insert($data);
        if($id > 0) {
            $sign_arr = array(
                "Id" => [
                    "integer" => $id
                ],
                "Reference name" => [
                    "text" => $this->reference_name
                ],
                "Name ru" => [
                    "text" => $data["name_ru"]
                ],
                "Name kk" => [
                    "text" => $data["name_kk"]
                ],
                "Signer iin" => [
                    "integer" => $this->getIin()
                ],
                "Action" => [
                    "text" => "Add"
                ],
                "Signer Fullname" => [
                    "text" => $certInfo["full_name"]
                ],
                "Sign date" => [
                    "datetime" => date("Y-m-d H:i:s")
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)){
                $log = array(
                    "admin_id" => $this->getAdminId(),
                    "admin_full_name" => $certInfo["full_name"],
                    "reference_name" => $this->reference_name,
                    "action" => "add",
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    throw new DomainException("Reference not added");
                }
            } else {
                throw new DomainException("Error to sign action");
            }
        } else {
            throw new DomainException("Applicant not added");
        }
    }
}