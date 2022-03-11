<?php

namespace App\Domain\Position\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class PositionFinderRepository
{
    /**
     * @var string
     */
    public static $tableName = 'rb_positions';

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
        $query->select(["id", "name_".$lang." as name"]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     *
     * @param int $id The id
     * @param string $lang The language
     *
     * @return array<mixed> The list view data
     */
    public function findByIdAndLang(int $id, string $lang): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select([ "name_".$lang." as name"])->where(["id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get all by ids on db 
     *
     * @param array<int> $lang The language
     * @param string $lang The language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByIdsAndLang(array $ids, string $lang): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["id", "name_".$lang." as name"])->whereInList("id", $ids);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Get all 
     *
     * @return array<mixed> The list view data
     */
    public function getAll(): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
