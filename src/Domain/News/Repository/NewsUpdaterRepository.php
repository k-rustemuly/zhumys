<?php

namespace App\Domain\News\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class NewsUpdaterRepository{
    /**
     * @var string
     */
    public static $tableName = 'news';

    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory){
        $this->queryFactory = $queryFactory;
    }

    /**
     * Update row.
     *
     * @param string $bin 
     * @param int $id 
     * @param array<mixed> $data The data
     *
     * @return void
     */
    public function updateByBinAndId(string $bin, int $id, array $data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("bin" => $bin))->execute()->rowCount();
        }catch(PDOException $e){
            throw new DomainException("Error on update row");
        }
    }
}
