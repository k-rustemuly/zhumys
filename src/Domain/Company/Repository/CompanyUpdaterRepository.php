<?php

namespace App\Domain\Company\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class CompanyUpdaterRepository{
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
    public function __construct(QueryFactory $queryFactory){
        $this->queryFactory = $queryFactory;
    }

    /**
     * Update row.
     *
     * @param string $bin 
     * @param array<mixed> $data The data
     *
     * @return void
     */
    public function updateByBin(string $bin, array $data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("bin" => $bin))->execute()->rowCount();
        }catch(PDOException $e){
            throw new DomainException("Error on update row");
        }
    }
}
