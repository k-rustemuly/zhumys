<?php

namespace App\Domain\Ranging\Repository;

use App\Factory\QueryFactory;
use App\Domain\RangingStatus\Repository\RangingStatusFinderRepository;
use App\Domain\Privelege\Repository\PrivelegeReadRepository;
use App\Domain\FreePlace\Repository\FreePlaceReadRepository;
use App\Domain\Position\Repository\PositionFinderRepository;

/**
 * Repository.
 */
final class RangingReaderRepository {
    /**
     * @var string
     */
    public static $tableName = "ranging";

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
     * @param int $freePlaceId 
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByFreePlaceIdAndLang(int $freePlaceId, string $lang) :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.*",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(["s" => RangingStatusFinderRepository::$tableName], ["s.id = r.status_id"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = r.privilege_id"])
            ->where(["r.free_place_id" => $freePlaceId]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param int $id 
     * @param string $bin
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function findByIdAndBinAndLang(int $id, string $bin, string $lang = "ru") :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.*",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(["s" => RangingStatusFinderRepository::$tableName], ["s.id = r.status_id"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = r.privilege_id"])
            ->innerJoin(["f" => FreePlaceReadRepository::$tableName], ["f.id = r.free_place_id"])
            ->where([ "r.id" => $id, "f.bin" => $bin]);
        return $query->execute()->fetch("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param int $freePlaceId 
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByFreePlaceId(int $freePlaceId) :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.id", "r.status_id"])->where(["r.free_place_id" => $freePlaceId]);
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param int $id 
     * @param int $freePlaceId 
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function findByIdAndFreePlaceIdAndLang(int $id, int $freePlaceId, string $lang = "ru") :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.*",
                        "s.name_".$lang." as status_name",
                        "s.color as status_color",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(["s" => RangingStatusFinderRepository::$tableName], ["s.id = r.status_id"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = r.privilege_id"])
            ->innerJoin(["f" => FreePlaceReadRepository::$tableName], ["f.id = r.free_place_id"])
            ->where(["r.free_place_id" => $freePlaceId, "r.id" => $id]);
        return $query->execute()->fetch("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param string $lang The lang
     * @param string $bin
     * @param int $status_id 
     * @param int $limit 
     * @param array $orderAsc
     * @param array $orderDesc
     * 
     * @return array<mixed> The list view data
     */
    public function search(string $lang, string $bin, int $status_id = 2, int $limit = 0, array $orderAsc = array(), array $orderDesc = array()) :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.id", "r.id as ranging_id", "r.full_name", "r.birthdate", "r.privilege_id", "r.positions", "r.phone_number", "r.free_place_id",
                        "r.interview_date", "r.interview_time",
                        "po.name_".$lang." as position_name",
                        "p.name_".$lang." as privilege_name"])
            ->innerJoin(["p" => PrivelegeReadRepository::$tableName], ["p.id = r.privilege_id"])
            ->innerJoin(["f" => FreePlaceReadRepository::$tableName], ["f.id = r.free_place_id"])
            ->innerJoin(["po" => PositionFinderRepository::$tableName], ["po.id = f.position_id"])
            ->where(["r.status_id" => $status_id, "f.bin" => $bin]);
            foreach ($orderAsc as $field) {
                $query->orderAsc("r.".$field);
            }
            foreach ($orderDesc as $field) {
                $query->orderDesc("r.".$field);
            }
            if($limit > 0) {
                $query->limit($limit);
            }
        return $query->execute()->fetchAll("assoc") ?: [];
    }

    /**
     * Get All data from db
     *
     * @param int $id 
     * @param int $freePlaceId 
     * @param string $bin
     * 
     * @return array<mixed> The list view data
     */
    public function findByIdAndFreePlaceIdAndBin(int $id, int $freePlaceId, string $bin) :array{
        $query = $this->queryFactory->newSelect(["r" => self::$tableName]);
        $query->select(["r.*"])
            ->innerJoin(["f" => FreePlaceReadRepository::$tableName], ["f.id = r.free_place_id"])
            ->where([ "r.id" => $id, "f.bin" => $bin, "r.free_place_id" => $freePlaceId]);
        return $query->execute()->fetch("assoc") ?: [];
    }
}
