<?php

namespace App\Domain\Applicant\Log\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class ApplicantLogFinderRepository {
    /**
     * @var string
     */
    public static $tableName = "applicants_logs";

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
     * Find data by id from db
     * 
     * @param int $id
     * @param string $lang
     *
     * @return array<mixed> The list view data
     */
    public function getByApplicantId(int $id): array{
        $query = $this->queryFactory->newSelect(["l" => self::$tableName]);
        $query->select(["l.*"])
            ->where(["l.applicant_id" => $id])->orderAsc("l.created_at");
        return $query->execute()->fetch("assoc") ?: [];
    }
}
