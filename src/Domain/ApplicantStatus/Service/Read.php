<?php

namespace App\Domain\ApplicantStatus\Service;

use App\Domain\ApplicantStatus\Repository\ApplicantStatusFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Read {
    /**
     * @var ApplicantStatusFinderRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param ApplicantStatusFinderRepository $repository The repository
     */
    public function __construct(ApplicantStatusFinderRepository $repository) {
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
    public function get(string $lang): array{   
        return $this->repository->getAllByLang($lang);
    }
}
