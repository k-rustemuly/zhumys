<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use DomainException;

/**
 * Service.
 */
final class Publish extends Admin{

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     *
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository) {
        $this->readRepository = $readRepository;
    }

    /**
     * Publish to free places
     *
     * @param int $id The id of free place
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return array<mixed>
     */
    public function publish(int $id, array $post) {
        //TODO: publish free place candidates
    }
}