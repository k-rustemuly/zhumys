<?php

namespace App\Domain\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use DomainException;
use Predis\ClientInterface;

/**
 * Service.
 */
final class Publish extends Admin {

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var ClientInterface
     */
    private $redis;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param ClientInterface         $redis
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository,
                                ClientInterface $redis) {
        $this->readRepository = $readRepository;
        $this->redis = $redis;
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