<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class Image {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "base64",
        "accept" => "image/jpeg,image/png,image/jpg",
        "max_allowed_size" => "10",
        "value" => null
    );
}