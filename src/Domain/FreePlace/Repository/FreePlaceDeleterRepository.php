<?php

namespace App\Domain\FreePlace\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class FreePlaceDeleterRepository{
    /**
     * @var string
     */
    public static $tableName = 'free_places';

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
     * @param array<mixed> $data The user data to update
     * @param array<mixed> $where The where
     *
     * @return void
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
