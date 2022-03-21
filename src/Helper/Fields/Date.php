<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class Date {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "date",
        "min_date" => null,
        "max_date" => null,
        "is_picker" => false,
        "value" => null
    );
}