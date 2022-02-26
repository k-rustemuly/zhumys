<?php

namespace App\Domain\Admin\Center\Repository;

use App\Factory\QueryFactory;

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
}
