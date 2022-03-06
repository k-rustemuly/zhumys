<?php

namespace App\Domain\Privelege\Service;

use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use DomainException;

/**
 * Service.
 */
final class Read
{
    /**
     * @var PrivelegeReadRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param PrivelegeReadRepository $repository The repository
     */
    public function __construct(PrivelegeReadRepository $repository)
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
