<?php

namespace App\Domain\Ranging\Repository;

use App\Factory\QueryFactory;
use App\Domain\RangingStatus\Repository\RangingStatusFinderRepository;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;

/**
 * Repository.
 */
final class RangingReaderRepository{
    /**
     * @var string
     */
    public static $tableName = 'ranging';

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
     * @param int $freePlaceId 
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByFreePlaceIdAndLang(int $freePlaceId, string $lang): array{
        $query = $this->queryFactory->newSelect(['r' => self::$tableName]);
        $query->select(["r.*",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(['s' => RangingStatusFinderRepository::$tableName], ['s.id = r.status_id'])
            ->innerJoin(['p' => PrivelegeReadRepository::$tableName], ['p.id = r.privilege_id'])
            ->where(["r.free_place_id" => $freePlaceId]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }
}
