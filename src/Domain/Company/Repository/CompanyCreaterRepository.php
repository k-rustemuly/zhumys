<?php

namespace App\Domain\Company\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class CompanyCreaterRepository
{
    /**
     * @var string
     */
    public static $tableName = 'company';

    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Insert user row.
     *
     * @param array<mixed> $row The user data
     *
     * @return int The new ID
     */
    public function insert(array $row): int
    {
        try
        {
            return (int) $this->queryFactory->newInsert(self::$tableName, $row)->execute()->lastInsertId();
        }catch(PDOException $e){
            return 0;
        }
    }
}
