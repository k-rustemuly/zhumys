<?php

namespace App\Domain\CompanyEmployee\Repository;

use App\Factory\QueryFactory;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use App\Domain\ApplicantStatus\Repository\ApplicantStatusFinderRepository;

/**
 * Repository.
 */
final class CompanyEmployeeReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'company_employees';

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
     * Get All data from db
     * 
     * @param string $lang interface language
     * @param string $bin 
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLangAndBin(string $lang, string $bin): array{
        $query = $this->queryFactory->newSelect(["ce" => self::$tableName]);
        $query->select(["ce.id", "ce.full_name", "ce.birthdate", "ce.privilege_id", "ce.positions", "ce.phone_number", "ce.created_at",
                        "p.name_".$lang." as privilege_name",])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = ce.privilege_id"])
            ->where(["ce.bin" => $bin])
            ->orderDesc("ce.created_at");
        return $query->execute()->fetchAll("assoc") ?: [];
    }
}
