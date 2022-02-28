<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Company\Admin;
use App\Domain\FreePlace\Repository\FreePlaceCreaterRepository;
use DomainException;
use App\Helper\Pki;
use App\Helper\Validator;

/**
 * Service.
 */
final class Add extends Admin{

    /**
     * @var FreePlaceCreaterRepository
     */
    private $createRepository;

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
     * @param Pki               $pki The pki client
     *
     */
    public function __construct(FreePlaceCreaterRepository $createRepository, Pki $pki) {
        $this->createRepository = $createRepository;
        $this->pki = $pki;
        $this->validator = new Validator();
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $data = $this->validator->setConfig(Read::getHeader())->validateOnCreate($post);

        $certInfo = $this->pki->getCertificateInfo($post["sign_p12"], $post["password"], false);
        if(!$certInfo["is_individual"]) {
            throw new DomainException("Only individual usage digital signature accessed");
        }
        $iin = (string)$certInfo["iin"];
        if($iin != $this->getIin()) {
            throw new DomainException("The owner not does not match the certificate auth");
        }
        return $data;
    }
}