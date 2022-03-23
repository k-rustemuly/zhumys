<?php

namespace App\Domain\CompanyNews\Repository;

use App\Factory\QueryFactory;
use PDOException;
use App\Domain\Language\Repository\LanguageFinderRepository;

/**
 * Repository.
 */
final class NewsFinderRepository {
    /**
     * @var string
     */
    public static $tableName = "news";

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
     * @param string $lang The lang
     * @param string $bin
     * @param int $limit 
     * @param array $orderAsc
     * @param array $orderDesc
     * 
     * @return array<mixed> The list view data
     */
    public function search(string $lang, string $bin, int $limit = 0, array $orderAsc = array(), array $orderDesc = array()) :array{
        $query = $this->queryFactory->newSelect(["n" => self::$tableName]);
        $query->select(["n.*", 
                        "l.name_".$lang." as language_name"])
            ->innerJoin(["l" => LanguageFinderRepository::$tableName], ["l.id = n.lang"])
            ->where(["n.bin" => $bin]);
            foreach ($orderAsc as $field) {
                $query->orderAsc("n.".$field);
            }
            foreach ($orderDesc as $field) {
                $query->orderDesc("n.".$field);
            }
            if($limit > 0) {
                $query->limit($limit);
            }
        return $query->execute()->fetchAll("assoc") ?: [];
    }
}
