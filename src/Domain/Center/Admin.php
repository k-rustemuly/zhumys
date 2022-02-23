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
     * The init
     *
     */
    public function init(array $data){
        $this->id = $data['id'];
        $this->bin = $data['bin'];
    }

    public function getAdminId() :int{
        return $this->id;
    }

    public function getOrgBin() :string{
        return $this->bin;
    }
}