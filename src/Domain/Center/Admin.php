<?php

namespace App\Domain\Center;

/**
 * Service.
 */
class Admin{

    /**
     * @var int
     */
    public $id;

    /**
     * @var string
     */
    public $bin;

    /**
     * @var string
     */
    public $iin;

    /**
     * The init
     *
     */
    public function init(array $data){
        $this->id = $data['id'];
        $this->bin = $data['org_bin'];
        $this->iin = $data['iin'];
    }

    public function getAdminId() :int{
        return $this->id;
    }

    public function getOrgBin() :string{
        return $this->bin;
    }

    public function getIin() :string{
        return $this->iin;
    }
}