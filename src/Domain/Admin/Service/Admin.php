<?php

namespace App\Domain\Admin\Service;

use App\Domain\Admin\Repository\AdminsFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Admin
{
    /**
     * @var AdminsFinderRepository
     */
    private $repository;

    /**
     * @var Language
     */
    private $language;

    /**
     * The constructor.
     *
     * @param AdminsFinderRepository $repository The repository
     */
    public function __construct(AdminsFinderRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Get handbook.
     *
     * @param string $lang interface language
     *
     * @throws DomainException
     * 
     * @return array<mixed> The result
     */
    public function get(string $lang): array
    {   
        return $this->repository->getAllByLang($lang);
    }
}
