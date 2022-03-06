<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use DomainException;
use App\Domain\Applicant\Repository\ApplicantReadRepository;

/**
 * Service.
 */
final class Generate extends Admin{

    /**
     * @var FreePlaceReadRepository
     */
    private $readRepository;

    /**
     * @var ApplicantReadRepository
     */
    private $applicantRepository;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param ApplicantReadRepository $applicantRepository
     *
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository,
                                ApplicantReadRepository $applicantRepository) {
        $this->readRepository = $readRepository;
        $this->applicantRepository = $applicantRepository;
    }

    /**
     * Generate candidates to free places
     *
     * @param int $id The id of free place
     * @param array<mixed> $post fileds The post fields
     *
     * @throws DomainException
     * 
     * @return array<mixed>
     */
    public function generate(int $id, array $post) {
        $freePlaceInfo = $this->readRepository->findById($id);
        if($freePlaceInfo["status_id"] != 3) {
            throw new DomainException("Free place status must be accepted");
        }
        $candidates = $this->applicantRepository->getCandidates(1, 2, 1, 15);
        return array(
            "hash" => "1fgdSDF5d2g4dOIEf55d2r4gs",
            "candidates" => $candidates
        );
    }
}