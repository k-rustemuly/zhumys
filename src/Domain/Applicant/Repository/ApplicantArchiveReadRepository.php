<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use App\Domain\ApplicantStatus\Repository\ApplicantStatusFinderRepository;
use App\Domain\Ranging\Repository\RangingCreatorRepository;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Repository.
 */
final class ApplicantArchiveReadRepository {
    /**
     * @var string
     */
    public static $tableName = "applicant_archive";

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
     * @param string $lang interface language
     * @param int $status_id
     * @param int $privilege_id
     *
     * @return array<mixed> The list view data
     */
    public function getAllBySearch(string $lang, int $privilege_id = 0): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "c.name_".$lang." as company_name",
                        "p.name_".$lang." as privilege_name",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"])
            ->innerJoin(["s" => ApplicantStatusFinderRepository::$tableName], ["s.id = a.status_id"]);
            if($privilege_id > 0) {
                $query->where(["a.privilege_id" => $privilege_id]);                
            }
        return $query->execute()->fetchAll("assoc") ?: [];
    }
}
