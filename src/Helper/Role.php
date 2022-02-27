<?php 
declare(strict_types=1);

namespace App\Helper;

class Role{

    /**
     * Admin is center organization
     *
     * @param array       $data
     *
     * @return boolean
     */
    public static function isCenterAdmin(array $data) :bool{
        return (isset($data['org_type']) && $data['org_type'] == 'center' && isset($data['type']) && $data['type'] == 'admin')? true : false;
    }

    /**
     * Admin is company organization
     *
     * @param array       $data
     * 
     * @return boolean
     */
    public static function isCompanyAdmin(array $data) :bool{
        return (isset($data['org_type']) && $data['org_type'] == 'company' && isset($data['type']) && $data['type'] == 'admin')? true : false;
    }
}