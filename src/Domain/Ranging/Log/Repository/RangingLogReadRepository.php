<?php

namespace App\Domain\Ranging\Log\Repository;

use App\Factory\QueryFactory;
use App\Domain\RangingStatus\Repository\RangingStatusFinderRepository;
use App\Domain\Company\Repository\CompanyReadRepository;
use App\Domain\Ranging\Repository\RangingReaderRepository;
/**
 * Repository.
 */
final class RangingLogReadRepository {
    /**
     * @var string
     */
    public static $tableName = "ranging_logs";

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Get All data from db
     *
     * @param int $id The id
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByIdAndLang(int $id, string $lang) :array{
        $query = $this->queryFactory->newSelect(["rl" => self::$tableName]);
        $query->select(["rl.id", "rl.admin_full_name", "rl.reason", "rl.created_at",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "c.name_".$lang." as company_name"])
            ->innerJoin(["s" => RangingStatusFinderRepository::$tableName], ["s.id = rl.status_id"])
            ->innerJoin(["c" => CompanyReadRepository::$tableName], ["c.bin = rl.company_bin"])
            ->where(["rl.ranging_id" => $id])
            ->order(["rl.created_at" => "ASC"]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param int $id The id
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByApplicantIdAndLang(int $id, string $lang) :array{
        $query = $this->queryFactory->newSelect(["rl" => self::$tableName]);
        $query->select(["rl.id", "rl.admin_full_name", "rl.reason", "rl.created_at",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "c.name_".$lang." as company_name"])
            ->innerJoin(["s" => RangingStatusFinderRepository::$tableName], ["s.id = rl.status_id"])
            ->innerJoin(["c" => CompanyReadRepository::$tableName], ["c.bin = rl.company_bin"])
            ->innerJoin(["r" => RangingReaderRepository::$tableName], ["r.id = rl.ranging_id"])
            ->where(["r.applicant_id" => $id])
            ->order(["rl.created_at" => "ASC"]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

}
