<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;

/**
 * Repository.
 */
final class ApplicantReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'applicant';

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
     * @return array<mixed> The list view data
     */
    public function getAll(): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Get All data from db
     * 
     * @param string $lang interface language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array{
        $query = $this->queryFactory->newSelect(["a" => self::$tableName]);
        $query->select(["a.*",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = a.privilege_id"]);
            //->where(["ca.iin" => $iin]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Find data by id from db
     * 
     * @param int $id
     *
     * @return array<mixed> The list view data
     */
    public function findById(int $id): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(array('id' => $id));
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Find data by id from db
     * 
     * @param string $iin
     *
     * @return array<mixed> The list view data
     */
    public function findByIin(string $iin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(array('iin' => $iin));
        return $query->execute()->fetch('assoc') ?: [];
    }
}
