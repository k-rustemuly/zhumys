<?php

namespace App\Domain\Center\Company\Service;

use App\Domain\Company\Repository\CompanyReadRepository;
use DomainException;

/**
 * Service.
 */
final class Delete{

    /**
     * @var CompanyReadRepository
     */
    private $readRepository;

    /**
     * @var Render
     */
    private $render;

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
    public function update(array $data) :array{
        return Read::getHeader();
    }
}