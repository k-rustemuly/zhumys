<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;

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
}
