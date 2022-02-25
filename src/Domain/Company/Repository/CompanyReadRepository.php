<?php

namespace App\Domain\Company\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class CompanyReadRepository
{
    /**
     * @var string
     */
    public static $tableName = 'company';

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
     * Get data from db by bin
     *
     * @param string $bin The bin
     *
     * @return array<mixed> The list view data
     */
    public function getByBin(string $bin): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["bin" => $bin]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get data from db by id
     *
     * @param string $id The id
     *
     * @return array<mixed> The list view data
     */
    public function getById(int $id): array
    {
        $query = $this->queryFactory->newSelect(self::$tableName);
        $query->select(["*"])->where(["id" => $id]);
        return $query->execute()->fetch('assoc') ?: [];
    }

    /**
     * Get All data from db
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
