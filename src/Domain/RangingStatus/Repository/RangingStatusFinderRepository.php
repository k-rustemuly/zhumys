<?php

namespace App\Domain\RangingStatus\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class RangingStatusFinderRepository
{
    /**
     * @var string
     */
    public static $tableName = 'rb_ranging_statuses';

    /**
     * @var QueryFactory
     */
    private $queryFactory;

    /**
     * Constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory)
    {
        $this->queryFactory = $queryFactory;
    }
    /**
     * Get all country on db 
     *
     * @param string $lang The language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["id", "name_".$lang." as name", "color"]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
