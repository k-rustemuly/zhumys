<?php

namespace App\Domain\FreePlace\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class FreePlaceReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'free_places';

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
     * @param string $bin The bin
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByBin(string $bin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["bin" => $bin]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
