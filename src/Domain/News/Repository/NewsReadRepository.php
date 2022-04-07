<?php

namespace App\Domain\News\Repository;

use App\Factory\QueryFactory;
use App\Domain\Position\Repository\PositionFinderRepository;
use App\Domain\PlaceStatus\Repository\PlaceStatusFinderRepository;

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

}
