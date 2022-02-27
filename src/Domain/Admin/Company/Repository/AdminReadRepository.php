<?php

namespace App\Domain\Admin\Company\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class AdminReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'company_admins';

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
     * Get data from db by company bin
     *
     * @param string $bin The bin
     *
     * @return array<mixed> The list view data
     */
    public function getByBin(string $bin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["org_bin" => $bin]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Get data from db by iin
     *
     * @param string $iin The iin
     *
     * @return array<mixed> The list view data
     */
    public function findByIin(string $iin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["iin" => $iin]);
        return $query->execute()->fetch('assoc') ?: [];
    }
}
