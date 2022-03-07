<?php

namespace App\Domain\Center\FreePlace\Service;

use App\Domain\Center\Admin;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use DomainException;
use App\Domain\Applicant\Repository\ApplicantReadRepository;
use Predis\ClientInterface;

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
     * @var ClientInterface
     */
    private $redis;

    /**
     * The constructor.
     * @param FreePlaceReadRepository $readRepository
     * @param ApplicantReadRepository $applicantRepository
     * @param ClientInterface         $redis
     *
     */
    public function __construct(
                                FreePlaceReadRepository $readRepository,
                                ApplicantReadRepository $applicantRepository,
                                ClientInterface $redis) {
        $this->readRepository = $readRepository;
        $this->applicantRepository = $applicantRepository;
        $this->redis = $redis;
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
        $candidates = array();
        $default = array("1" => $post["1"]);
        unset($post["1"]);
        foreach($post as $privilege_id => $count) {
            $founded = $this->applicantRepository->getCandidates($id, 1, $privilege_id, $count);
            $candidates = array_merge($candidates, $founded);
            if(count($founded) < $count) {
                $free = $count - count($founded);
                $default["1"]+= $free;
            }
        }
        throw new DomainException(implode(" ", $candidates));
        foreach($default as $privilege_id => $count) {
            $founded = $this->applicantRepository->getCandidates($id, 1, $privilege_id, $count);
            $candidates = array_merge($candidates, $founded);
        }
        $hash = $this->generateHash();
        $dataToSave = $this->mapToSavedData($id, $candidates);
        $this->redis->setex($hash, $_ENV["GENERATED_APPLICANT_SAVE_SEC"], json_encode($dataToSave, JSON_UNESCAPED_UNICODE));

        return array(
            "hash" => $hash,
            "candidates" => $candidates
        );
    }

    /**
     * Mapping save data to redis client
     * 
     * @param int $id free place id
     * @param array<mixed> $candidates
     * 
     * @return array<mixed>
     */
    private function mapToSavedData(int $id, array $candidates) :array {
        $candidates_ids = array();
        foreach ($candidates as $candidate) {
            $candidates_ids[] = $candidate['id'];
        }
        return array(
            "free_place_id" => $id,
            "candidates_ids" => $candidates_ids
        );
    }

    /**
     *  Generating new refresh token for user
     *
     * @return string The refresh token
     */
    private function generateHash(int $length = 36, int $attempt = 1) :string{
        $randomStr = $this->base64url_encode(substr(hash("sha512", mt_rand()), 0, $length));
        if($this->redis->exists($randomStr))
        {
            if($attempt > 10)
            {
                $attempt = 1;
                $length++;
            }
            return $this->generateHash($length, $attempt);
        }
        return $randomStr;
    }

    /**
     *
     * @param string $data The data
     *
     * @return string The cleaned string
     */
    private function base64url_encode(string $data) :string{ 
        return rtrim(strtr(base64_encode($data), "+/", "-_"), "="); 
    }
}