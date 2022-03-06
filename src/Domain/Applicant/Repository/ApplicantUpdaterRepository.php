<?php

namespace App\Domain\Applicant\Repository;

use App\Factory\QueryFactory;
use PDOException;
use DomainException;

/**
 * Repository.
 */
final class ApplicantUpdaterRepository{
    /**
     * @var string
     */
    public static $tableName = 'applicant';

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
     * Update row.
     *
     * @param string $iin The iin
     * @param array<mixed> $where The where
     *
     * @return void
     */
    public function updateByIin(string $iin, array $data): int{
        try
        {
            return (int) $this->queryFactory->newUpdate(self::$tableName, $data)->where(array("iin" => $iin))->execute()->rowCount();
        }catch(PDOException $e){
            throw new DomainException($e->getMessage());
            return 0;
        }
    }
}
