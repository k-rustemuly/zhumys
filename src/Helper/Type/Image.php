<?php 
declare(strict_types=1);

namespace App\Helper\Type;
use App\Helper\Type;

class Image extends Type{

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "base64",
        "required" => false,
        "can_change" => false,
        "accept" => "image/jpeg,image/png,image/jpg",
        "max_allowed_size" => "10",
        "name" => null,
        "hint" => null,
        "value" => null
    );
}