<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class PhoneNumber {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "phone_number",
        "mask" => "+7(xxx)xxx-xx-xx",
        "value" => null
    );
}