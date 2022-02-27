<?php 
declare(strict_types=1);

namespace App\Helper;
use DomainException;

class Field{

    /**
     * @var array<mixed>
     */
    private $original = array(
        "type" => "string",
        "is_required" => false,
        "can_update" => false,
        "can_create" => false,
        "is_visible" => true,
        "name" => null,
        "hint" => null
    );

    /**
     * @var array<mixed>
     */
    public $array = array();

    public static $_instance = null;

    public static function getInstance ()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }
        return self::$_instance;
    }

    public function init(object $object) :self {
        $this->array = array_merge($this->original, $object->array);
        return $this;
    }

	public function __call($method, $args) :self{
		if (!in_array($method, array_keys($this->array))) {
			throw new DomainException("Method not found");
		}
        $this->array[$method] = $args[0];
		return $this;
	}

    public function execute() :array {
        return $this->array;
    }
}