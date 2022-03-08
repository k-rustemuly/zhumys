<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use App\Domain\ApplicantStatus\Repository\ApplicantStatusFinderRepository;
use DomainException;

/**
 * Repository.
 */
final class ApplicantReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'applicant';

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory){
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
        return $query->execute()->fetchAll('assoc') ?: [];
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
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"]);
            //->where(["ca.iin" => $iin]);
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
        $query->select(["*"])->where(array('id' => $id));
        return $query->execute()->fetch('assoc') ?: [];
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
        $query->select(["*"])->where(array('iin' => $iin));
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get data by position,status,privilege Id and count from db
     * 
     * @param int $position_id
     * @param int $status_id
     * @param int $privilege_id
     * @param int $count
     *
     * @return array<mixed> The list view data
     */
    public function getCandidates(int $position_id, int $status_id, int $privilege_id, int $count): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["id", "full_name", "iin"])
        ->where(["privilege_id" => $privilege_id, "positions LIKE" => "%@".$position_id."@%", "status_id" => $status_id])
        ->orderAsc("raiting_number")
        ->limit($count);
        return $query->execute()->fetchAll('assoc') ?: [];
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
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
