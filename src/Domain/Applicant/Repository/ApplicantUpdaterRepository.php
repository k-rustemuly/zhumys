<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class ApplicantUpdaterRepository {
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
     * Update row.
     *
     * @param string $iin The iin
     * @param array<mixed> $where The where
     *
     * @return int
     */
    public function updateByIin(string $iin, array $data): int{
        try {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("iin" => $iin))->execute()->rowCount();
        } catch(PDOException $e) {
            return 0;
        }
    }

    /**
     * Update row for ranging
     *
     * @param array<int> $ids The ids
     *
     * @return int
     */
    public function updateByIdsRanging(array $ids): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, array("status_id" => 2))->whereInList("id", $ids)->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }

    /**
     * Update row for interview
     *
     * @param int $id The id
     *
     * @return int
     */
    public function updateByIdInterview(int $id): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, array("status_id" => 3))->where(["id" => $id])->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }

    /**
     * Update row for interview
     *
     * @param int $id The id
     *
     * @return int
     */
    public function updateByIdReject(int $id): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, array("status_id" => 1))->where(["id" => $id])->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }

    /**
     * Update row for interview
     *
     * @param int $id The id
     *
     * @return int
     */
    public function updateByIdAccept(int $id): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, array("status_id" => 4))->where(["id" => $id])->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }
}
