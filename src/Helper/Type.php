<?php 
declare(strict_types=1);

namespace App\Helper;

use DomainException;

class Type{

    public $array;

    public static $_instance = null;

    public static function getInstance ()
    {
        if (self::$_instance === null) {
            self::$_instance = new self;
        }

        return self::$_instance;
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