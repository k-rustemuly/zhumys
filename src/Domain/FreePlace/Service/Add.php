<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Company\Admin;
use App\Domain\FreePlace\Repository\FreePlaceCreaterRepository;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\FreePlace\Log\Repository\LogCreatorRepository;
use App\Domain\FreePlace\Repository\FreePlaceDeleterRepository;
use DomainException;
use App\Helper\Pki;
use App\Helper\Validator;
use Spatie\ArrayToXml\ArrayToXml;

/**
 * Service.
 */
final class Add extends Admin{
    /**
     * @var int
     */
    private $admin_type_id = 1;

    /**
     * @var FreePlaceCreaterRepository
     */
    private $createRepository;

    /**
     * @var LogCreatorRepository
     */
    private $logCreateRepository;

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var FreePlaceDeleterRepository
     */
    private $deleteRepository;

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
     * @param FreePlaceCreaterRepository $createRepository
     * @param FreePlaceReadRepository $readRepository
     * @param LogCreatorRepository $logCreateRepository
     * @param FreePlaceDeleterRepository $deleteRepository
     * @param Pki               $pki The pki client
     *
     */
    public function __construct(FreePlaceCreaterRepository $createRepository,
                                Pki $pki,
                                FreePlaceReadRepository $readRepository,
                                LogCreatorRepository $logCreateRepository,
                                FreePlaceDeleterRepository $deleteRepository) {
        $this->createRepository = $createRepository;
        $this->pki = $pki;
        $this->readRepository = $readRepository;
        $this->logCreateRepository = $logCreateRepository;
        $this->deleteRepository = $deleteRepository;
        $this->validator = new Validator();
    }

    /**
     * Sign in company admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);
        $sign_p12 = $post["sign_p12"];
        $password = $post["password"];
        $certInfo = $this->pki->getCertificateInfo($sign_p12, $password, false);
        if(!$certInfo["is_individual"]) {
            throw new DomainException("Only individual usage digital signature accessed");
        }
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        unset($data["sign_p12"]);
        unset($data["password"]);
        $data["bin"] = $this->getBin();
        $id = $this->createRepository->insert($data);
        if($id > 0) {
            $freePlaceInfo = $this->readRepository->findById($id);
            $sign_arr = array(
                "Id" => [
                    "integer" => (int)$freePlaceInfo["id"]
                ],
                "Company bin" => [
                    "integer" => $freePlaceInfo["bin"]
                ],
                "Author iin" => [
                    "integer" => $this->getIin()
                ],
                "Count" => [
                    "integer" => $freePlaceInfo["count"]
                ],
                "Comment" => [
                    "text" => $freePlaceInfo["comment"]
                ],
                "Status id" => [
                    "integer" => $freePlaceInfo["status_id"]
                ],
                "Reason" => [
                    "text" => $freePlaceInfo["reason"]
                ],
                "Created date" => [
                    "datetime" => $freePlaceInfo["created_at"]
                ]
            );
            $xml = ArrayToXml::convert($sign_arr);
            $signed_result = $this->pki->sign($xml, $sign_p12, $password);
            if(!empty($signed_result)){
                $log = array(
                    "free_place_id" => $id,
                    "admin_id" => $this->admin_type_id,
                    "admin_iin" => $iin,
                    "admin_full_name" => $certInfo["surname"]." ".$certInfo["name"]." ".$certInfo["lastname"],
                    "status_id" => $freePlaceInfo["status_id"],
                    "field" => $signed_result["raw"],
                    "sign" => $signed_result["xml"]
                );
                if($this->logCreateRepository->insert($log) == 0) {
                    $this->deleteRepository->deleteById($id);
                    throw new DomainException("Error to add free place");
                }
            } else {
                throw new DomainException("Error to add free place");
            }
        }else {
            throw new DomainException("Error to add free place");
        }
    }
}