<?php

namespace App\Domain\Company;

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
        $this->bin = $data['org_bin'];
    }

    public function getAdminId() :int{
        return $this->id;
    }

    public function getBin() :string{
        return $this->bin;
    }
}