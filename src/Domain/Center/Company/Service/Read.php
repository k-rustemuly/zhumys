<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyReadRepository;
use DomainException;
use App\Helper\Type\Image;

/**
 * Service.
 */
final class Read{

    /**
     * @var CompanyReadRepository
     */
    private $readRepository;

    /**
     * The constructor.
     * @param CompanyReadRepository $readRepository
     *
     */
    public function __construct(CompanyReadRepository $readRepository){
        $this->readRepository = $readRepository;
    }

    /**
     * Get company info
     *
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     */
    public function info(int $id) :array{
        return Image::getInstance()->execute();
    }
}