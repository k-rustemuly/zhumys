<?php

namespace App\Domain\Ranging\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class RangingDeleterRepository{
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
     * Delete row.
     *
     * @param int $free_place_id The free_place_id
     *
     * @return int
     */
    public function deleteByFreePlaceId(int $free_place_id): int
    {
        try
        {
            $statement = $this->queryFactory->newDelete(self::$tableName)->where(array("free_place_id" => $free_place_id))->execute();
            return $statement->count();
        }catch(PDOException $e){
            return 0;
        }
    }
}
