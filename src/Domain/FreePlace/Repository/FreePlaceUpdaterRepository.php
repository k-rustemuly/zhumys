<?php

namespace App\Domain\FreePlace\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class FreePlaceUpdaterRepository{
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
     * Update row.
     *
     * @param string $bin The company bin
     * @param int $id The id
     * @param array<mixed> $where The where
     *
     * @return void
     */
    public function updateByBinAndId(string $bin, int $id, array $data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("id" => $id, "bin" => $bin))->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }

    /**
     * Update row.
     *
     * @param int $id The id
     * @param array<mixed> $where The where
     *
     * @return void
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
