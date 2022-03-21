<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class File {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "base64",
        "accept" => "p12",
        "max_allowed_size" => "10",
        "value" => null
    );
}