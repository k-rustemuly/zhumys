<?php

namespace App\Domain\FreePlace\Log\Repository;

use App\Factory\QueryFactory;
use App\Domain\Admin\Repository\AdminsFinderRepository;
use App\Domain\PlaceStatus\Repository\PlaceStatusFinderRepository;

/**
 * Repository.
 */
final class LogReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'free_place_logs';

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
     * @param int $id The id
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByIdAndLang(int $id, string $lang): array{
        $query = $this->queryFactory->newSelect(['fpl' => self::$tableName]);
        $query->select(["fpl.*",
                        "a.name_".$lang." as admin_type_name",
                        "s.name_".$lang." as status_name"])
            ->innerJoin(['a' => AdminsFinderRepository::$tableName], ['a.id = fpl.admin_type_id'])
            ->innerJoin(['s' => PlaceStatusFinderRepository::$tableName], ['s.id = fpl.status_id'])
            ->where(["fpl.free_place_id" => $id])
            ->order(['fpl.created_at' => 'ASC']);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

}
