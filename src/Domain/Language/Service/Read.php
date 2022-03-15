<?php

namespace App\Domain\Language\Service;

use App\Domain\Language\Repository\LanguageFinderRepository as ReadRepository;
use DomainException;

/**
 * Service.
 */
final class Read
{
    /**
     * @var ReadRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param ReadRepository $repository The repository
     */
    public function __construct(ReadRepository $repository)
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
