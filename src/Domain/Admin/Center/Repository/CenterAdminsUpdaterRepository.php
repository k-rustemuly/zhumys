<?php

namespace App\Domain\Admin\Center\Repository;

use App\Factory\QueryFactory;

/**
 * Repository.
 */
final class CenterAdminsUpdaterRepository {
    /**
     * @var string
     */
    public static $tableName = "center_admins";

    /**
     * @var QueryFactory The query factory
     */
    private $queryFactory;

    /**
     * The constructor.
     *
     * @param QueryFactory $queryFactory The query factory
     */
    public function __construct(QueryFactory $queryFactory) {
        $this->queryFactory = $queryFactory;
    }

    /**
     * Update row
     *
     * @param string $iin The iin
     * @param array<mixed> $new_data The new data
     *
     * @return void
     */
    public function updateByIin(string $iin, array $new_data): int{
        return (int) $this->queryFactory->newUpdate(self::$tableName, $new_data)->where(["iin" => $iin])->execute()->rowCount();
    }
}
