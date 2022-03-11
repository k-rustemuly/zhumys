<?php

namespace App\Domain\Privelege\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class PrivelegeReadRepository
{
    /**
     * @var string
     */
    public static $tableName = 'rb_privileges';

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
     * Get all country on db 
     *
     * @param string $lang The language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array{
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
    public function findByIdAndLang(int $id, string $lang): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select([ "name_".$lang." as name"])->where(["id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get all
     *
     * @return array<mixed> The list view data
     */
    public function getAll() :array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->orderAsc("id");
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
