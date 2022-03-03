<?php

namespace App\Domain\FreePlace\Repository;

use App\Factory\QueryFactory;
use App\Domain\Position\Repository\PositionFinderRepository;
use App\Domain\PlaceStatus\Repository\PlaceStatusFinderRepository;
use App\Domain\Company\Repository\CompanyReadRepository;

/**
 * Repository.
 */
final class FreePlaceReadRepository{
    /**
     * @var string
     */
    public static $tableName = 'free_places';

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
     * @param string $bin The bin
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByBin(string $bin): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["bin" => $bin]);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Get All data from db
     *
     * @param string $lang The lang
     * @param string $bin The bin
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByBinAndLang(string $bin, string $lang): array{
        $query = $this->queryFactory->newSelect(['fp' => self::$tableName]);
        $query->select(["fp.*",
                        "p.name_".$lang." as position_name",
                        "s.name_".$lang." as status_name"])
            ->innerJoin(['p' => PositionFinderRepository::$tableName], ['p.id = fp.position_id'])
            ->innerJoin(['s' => PlaceStatusFinderRepository::$tableName], ['s.id = fp.status_id'])
            ->where(["fp.bin" => $bin])
            ->order(['fp.created_at' => 'DESC']);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

    /**
     * Find datas by id
     *
     * @param int $id The id
     * 
     * @return array<mixed> The list view data
     */
    public function findById(int $id): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Find datas by company bin and id
     *
     * @param string $bin The company bin
     * @param int $id The id
     * 
     * @return array<mixed> The list view data
     */
    public function findByBinAndId(string $bin, int $id): array{
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["id" => $id, "bin" => $bin]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Find datas by company bin and id
     *
     * @param string $bin The company bin
     * @param int $id The id
     * @param string $lang The lang
     * 
     * @return array<mixed> The list view data
     */
    public function findByBinAndIdAndLang(string $bin, int $id, string $lang): array{
        $query = $this->queryFactory->newSelect(['fp' => self::$tableName]);
        $query->select(["fp.*",
                        "p.name_".$lang." as position_name",
                        "s.name_".$lang." as status_name"])
            ->innerJoin(['p' => PositionFinderRepository::$tableName], ['p.id = fp.position_id'])
            ->innerJoin(['s' => PlaceStatusFinderRepository::$tableName], ['s.id = fp.status_id'])
            ->where(["fp.bin" => $bin, "fp.id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get All data from db
     *
     * @param string $lang The lang
     * @param string $bin The bin
     * 
     * @return array<mixed> The list view data
     */
    public function getAllByLang(string $lang): array{
        $query = $this->queryFactory->newSelect(['fp' => self::$tableName]);
        $query->select(["fp.*",
                        "c.name_".$lang." as company_name",
                        "p.name_".$lang." as position_name",
                        "s.name_".$lang." as status_name"])
            ->innerJoin(['p' => PositionFinderRepository::$tableName], ['p.id = fp.position_id'])
            ->innerJoin(['s' => PlaceStatusFinderRepository::$tableName], ['s.id = fp.status_id'])
            ->innerJoin(['c' => CompanyReadRepository::$tableName], ['c.bin = fp.bin'])
            ->where(["fp.status_id !=" => 1])
            ->order(['fp.created_at' => 'DESC']);
        return $query->execute()->fetchAll('assoc') ?: [];
    }

}
