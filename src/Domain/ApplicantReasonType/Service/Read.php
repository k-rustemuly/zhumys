<?php

namespace App\Domain\ApplicantReasonType\Service;

use App\Domain\ApplicantReasonType\Repository\ApplicantReasonTypeFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Read {
    /**
     * @var ApplicantReasonTypeFinderRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param ApplicantReasonTypeFinderRepository $repository The repository
     */
    public function __construct(ApplicantReasonTypeFinderRepository $repository) {
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
