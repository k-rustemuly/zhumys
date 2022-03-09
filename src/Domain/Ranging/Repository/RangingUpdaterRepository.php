<?php

namespace App\Domain\Ranging\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class RangingUpdaterRepository{
    /**
     * @var string
     */
    public static $tableName = 'ranging';

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
     * Update row.
     *
     * @param int $id The id
     * @param array<mixed> $where The where
     *
     * @return int
     */
    public function updateById(int $id, array $data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("id" => $id))->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }
}
