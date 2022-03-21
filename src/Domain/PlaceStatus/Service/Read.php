<?php

namespace App\Domain\PlaceStatus\Service;

use App\Domain\PlaceStatus\Repository\PlaceStatusFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Read {
    /**
     * @var PlaceStatusFinderRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param PlaceStatusFinderRepository $repository The repository
     */
    public function __construct(PlaceStatusFinderRepository $repository) {
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
