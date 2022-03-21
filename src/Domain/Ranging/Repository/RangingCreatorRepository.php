<?php

namespace App\Domain\Ranging\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class RangingCreatorRepository {
    /**
     * @var string
     */
    public static $tableName = "ranging";

    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Insert row.
     *
     * @param array<mixed> $row The data
     *
     * @return int The new ID
     */
    public function insert(array $row): int{
        try {
            return (int) $this->queryFactory->newInsert(self::$tableName, $row)->execute()->lastInsertId();
        } catch(PDOException $e) {
            return 0;
        }
    }
}
