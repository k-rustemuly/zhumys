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
    private $admin_type_id = 2;

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
        $data["bin"] = $this->getBin();
        $id = $this->createRepository->insert($data);
        if($id == 0) {
            throw new DomainException("Error to add free place");
        }
    }
}