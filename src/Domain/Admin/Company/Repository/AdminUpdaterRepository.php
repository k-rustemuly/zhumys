<?php

namespace App\Domain\Admin\Company\Repository;

use App\Factory\QueryFactory;
use PDOException;

/**
 * Repository.
 */
final class AdminUpdaterRepository{
    /**
     * @var string
     */
    public static $tableName = 'company_admins';

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
     * Update 
     *
     * @param int $id The id
     * @param array<mixed> $new_data The new data
     *
     * @return void
     */
    public function updateById(int $id, array $new_data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $new_data)->where(['id' => $id])->execute()->rowCount();
        }catch(PDOException $e){
            return 0;
        }
    }

    /**
     * Update row
     *
     * @param string $iin The iin
     * @param array<mixed> $new_data The new data
     *
     * @return void
     */
    public function updateByIin(string $iin, array $new_data): int
    {
        return (int) $this->queryFactory->newUpdate(self::$tableName, $new_data)->where(['iin' => $iin])->execute()->rowCount();
    }
}
