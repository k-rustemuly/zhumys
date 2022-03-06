<?php

namespace App\Domain\Admin\Center\Repository;

use App\Factory\QueryFactory;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Repository.
 */
final class CenterAdminsReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'center_admins';

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
     * Get data from db by iin
     *
     * @param string $iin The iin
     *
     * @return array<mixed> The list view data
     */
    public function getByIin(string $iin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["iin" => $iin]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Find datas by free place id
     *
     * @param string $iin The iin
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function findByIinAndLang(string $iin, string $lang): array{
        $query = $this->queryFactory->newSelect(['ca' => self::$tableName]);
        $query->select(["ca.*",
                        "c.name_".$lang." as company_name"])
            ->innerJoin(['c' => CompanyReadRepository::$tableName], ['c.bin = ca.org_bin'])
            ->where(["ca.iin" => $iin]);
        return $query->execute()->fetch('assoc') ?: [];
    }
}
