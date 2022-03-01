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
     * @param string $bin The company bin
     * @param int $id The id
     *
     * @return int
     */
    public function deleteByBinAndId(string $bin, int $id): int
    {
        try
        {
            $statement = $this->queryFactory->newDelete(self::$tableName)->where(array("id" => $id, "bin" => $bin, "status_id" => 1))->execute();
            return $statement->count();
        }catch(PDOException $e){
            return 0;
        }
    }
}
