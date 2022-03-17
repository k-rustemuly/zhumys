<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use App\Domain\ApplicantStatus\Repository\ApplicantStatusFinderRepository;
use App\Domain\Ranging\Repository\RangingCreatorRepository;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Repository.
 */
final class ApplicantReadRepository {
    /**
     * @var string
     */
    public static $tableName = "applicant";

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Get All data from db
     *
     * @return array<mixed> The list view data
     */
    public function getAll(): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     * 
     * @param string $lang interface language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "p.name_".$lang." as privilege_name",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"])
            ->where(["a.status_id !=" => 4]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     * 
     * @param string $lang interface language
     * @param int $privilege_id
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLangAndPrivilege(string $lang, int $privilege_id = 0): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "p.name_".$lang." as privilege_name",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"])
            ->where(["a.status_id" => 1]);
            if($privilege_id > 0) {
                $query->where(["a.privilege_id" => $privilege_id]);                
            }
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Find data by id from db
     * 
     * @param int $id
     *
     * @return array<mixed> The list view data
     */
    public function findById(int $id): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(array("id" => $id));
        return $query->execute()->fetch("assoc") ?: [];
    }

    /**
     * Find data by id from db
     * 
     * @param string $iin
     *
     * @return array<mixed> The list view data
     */
    public function findByIin(string $iin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(array("iin" => $iin));
        return $query->execute()->fetch("assoc") ?: [];
    }

    /**
     * Get data by position,status,privilege Id and count from db
     * 
     * @param int $position_id
     * @param int $status_id
     * @param int $privilege_id
     * @param int $count
     * @param string $lang
     *
     * @return array<mixed> The list view data
     */
    public function getCandidates(int $position_id, int $status_id, int $privilege_id, int $count, string $lang): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.id", "a.raiting_number", "a.full_name", "a.iin",
                        "p.name_".$lang." as privilege_name"])
        ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
        ->where(["a.privilege_id" => $privilege_id, "a.positions LIKE" => "%@".$position_id."@%", "a.status_id" => $status_id])
        ->orderAsc("a.raiting_number")
        ->limit($count);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get candiadates by candidates_ids
     * 
     * @param array<int> $ids
     * 
     * @return array<mixed> The list view data
     */
    public function getCandidatesByIds(array $ids) :array {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["id", "raiting_number", "iin", "full_name", "birthdate", "privilege_id", "positions", "email", "phone_number", "address", "second_phone_number", "comment"])
        ->whereInList("id", $ids)
        ->andWhere(["status_id" => 1]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Find data by id from db
     * 
     * @param int $id
     * @param string $lang
     *
     * @return array<mixed> The list view data
     */
    public function findByIdAndLang(int $id, string $lang): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "p.name_".$lang." as privilege_name",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"])
            ->where(["a.id" => $id]);
        return $query->execute()->fetch("assoc") ?: [];
    }

    /**
     * Get All data from db
     * 
     * @param string $lang interface language
     * @param int $status_id
     * @param int $privilege_id
     *
     * @return array<mixed> The list view data
     */
    public function getAllBySearch(string $lang, int $status_id = 0, int $privilege_id = 0): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "c.name_".$lang." as company_name",
                        "p.name_".$lang." as privilege_name",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"])
            ->innerJoin(["r" => RangingCreatorRepository::$tableName], ["r.applicant_id = a.id"])
            ->innerJoin(["f" => FreePlaceReadRepository::$tableName], ["f.id = r.free_place_id"])
            ->innerJoin(["c" => CompanyReadRepository::$tableName], ["c.bin = f.bin"])
            ->where(["a.status_id" => $status_id]);
            switch ($status_id) {
                case 2:
                    $query->where(["r.status_id" => 1]);
                break;
                case 3:
                    $query->where(["r.status_id" => 2]);
                break;
                case 4:
                    $query->where(["r.status_id" => 3]);
                break;
            }
            if($privilege_id > 0) {
                $query->where(["a.privilege_id" => $privilege_id]);                
            }
        return $query->execute()->fetchAll("assoc") ?: [];
    }
}
