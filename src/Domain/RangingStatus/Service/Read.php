<?php

namespace App\Domain\RangingStatus\Service;

use App\Domain\RangingStatus\Repository\RangingStatusFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Read {
    /**
     * @var RangingStatusFinderRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param RangingStatusFinderRepository $repository The repository
     */
    public function __construct(RangingStatusFinderRepository $repository) {
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
    public function get(string $lang) :array{
        return $this->repository->getAllByLang($lang);
    }
}
