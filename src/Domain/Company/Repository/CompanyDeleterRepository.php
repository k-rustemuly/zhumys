<?php

namespace App\Domain\Company\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class CompanyDeleterRepository{
    /**
     * @var string
     */
    public static $tableName = 'company';

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
     * @param int $id The id
     *
     * @return int
     */
    public function deleteById(int $id): int
    {
        try
        {
            $statement = $this->queryFactory->newDelete(self::$tableName)->where(array("id" => $id))->execute();
            return $statement->count();
        }catch(PDOException $e){
            return 0;
        }
    }
}
