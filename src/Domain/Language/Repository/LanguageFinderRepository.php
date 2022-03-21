<?php

namespace App\Domain\Language\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class LanguageFinderRepository {
    /**
     * @var string
     */
    public static $tableName = "rb_language";

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
     * Get all 
     *
     * @param string $lang The language
     *
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang) :array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["id", "name_".$lang." as name"]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }
}
