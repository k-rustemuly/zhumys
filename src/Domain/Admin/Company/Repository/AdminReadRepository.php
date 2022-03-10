<?php

namespace App\Domain\Admin\Company\Repository;

use App\Domain\Company\Repository\CompanyReadRepository;
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

    /**
     * Get data from db by id
     *
     * @param int $id The id
     *
     * @return array<mixed> The list view data
     */
    public function findById(int $id): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get data from db by lang
     *
     * @param string $lang The lang
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "c.name_".$lang." as company_name"])
        ->innerJoin(["с" => CompanyReadRepository::$tableName], ["c.bin = a.org_bin"]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
