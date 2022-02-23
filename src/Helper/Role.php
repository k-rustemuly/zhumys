<?php 
declare(strict_types=1);

namespace App\Helper;

class Role{
    /**
     * Открываем токен jwt 
     *
     * @param ServerRequestInterface       $request
     * @throws HttpUnauthorizedException
     *
     */
    public static function isCenterAdmin(array $data) :bool{
        return (isset($data['org_type']) && $data['org_type'] == 'center' && isset($data['type']) && $data['type'] == 'admin')? true : false;
    }
}