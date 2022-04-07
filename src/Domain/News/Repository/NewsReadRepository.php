<?php

namespace App\Domain\News\Repository;

use App\Factory\QueryFactory;
use App\Domain\Language\Repository\LanguageFinderRepository;


/**
 * Repository.
 */
final class NewsReadRepository {
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
     * @param string $bin The bin
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByBinAndLang(string $bin, string $lang) :array{
        $query = $this->queryFactory->newSelect(["n" => self::$tableName]);
        $query->select(["n.id", "n.image", "n.lang", "n.title", "n.created_at"])
            ->where(["n.bin" => $bin])
            ->order(["n.created_at" => "DESC"]);
        return $query->execute()->fetchAll("assoc") ?: [];
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
            ->where(["n.bin" => $bin, "n.is_public" => 1]);
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
