<?php 
declare(strict_types=1);

namespace App\Helper\Fields;

class Reference {

    /**
     * @var array<mixed>
     */
    public $array = array(
        "type" => "reference",
        "value" => null,
        "reference_name" => null,
        "reference_id" => null
    );
}