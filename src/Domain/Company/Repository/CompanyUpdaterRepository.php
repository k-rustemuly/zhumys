<?php

namespace App\Domain\Company\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class CompanyUpdaterRepository
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
     * Update row.
     *
     * @param array<mixed> $data The user data to update
     * @param array<mixed> $where The where
     *
     * @return void
     */
    public function updateById(int $id, array $data): int
    {
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("id" => $id))->execute()->rowCount();
        }catch(PDOException $e){
            throw new DomainException("Error on update row");
        }
    }
}
