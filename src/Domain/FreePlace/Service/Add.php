<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Company\Admin;
use App\Domain\FreePlace\Repository\FreePlaceCreaterRepository;
use DomainException;
use App\Helper\Pki;

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
     * The constructor.
     * @param FreePlaceCreaterRepository $createRepository
     * @param Pki               $pki The pki client
     *
     */
    public function __construct(FreePlaceCreaterRepository $createRepository, Pki $pki) {
        $this->createRepository = $createRepository;
        $this->pki = $pki;
    }

    /**
     * Sign in center admin by digital signature.
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function add(array $post) {
        $certInfo = $this->pki->getCertificateInfo($post["sign_p12"], $post["password"], false);
    }
}