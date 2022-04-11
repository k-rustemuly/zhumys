<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class ApplicantDeleterRepository {
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
     * Delete row.
     *
     * @param  $id The id
     *
     * @return int
     */
    public function deleteById(int $id): int{
        try {
            return (int) $this->queryFactory->newDelete(self::$tableName)->where(array("id" => $id))->execute()->rowCount();
        } catch(PDOException $e) {
            return 0;
        }
    }
}
