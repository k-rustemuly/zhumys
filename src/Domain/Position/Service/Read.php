<?php

namespace App\Domain\Position\Service;

use App\Domain\Position\Repository\PositionFinderRepository;
use DomainException;

/**
 * Service.
 */
final class Read
{
    /**
     * @var PositionFinderRepository
     */
    private $repository;

    /**
     * The constructor.
     *
     * @param PositionFinderRepository $repository The repository
     */
    public function __construct(PositionFinderRepository $repository)
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
