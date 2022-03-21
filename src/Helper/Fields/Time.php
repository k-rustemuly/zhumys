<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class Time {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "time",
        "min_time" => null,
        "max_time" => null,
        "is_picker" => false,
        "value" => null
    );
}