<?php
namespace App\Exception;

use DomainException;
use Throwable;

class FieldException extends DomainException {
    /**
     * @var array<mixed>
     */
    private $errors;

    public function __construct($message, $code = 0, Throwable $previous = null) {
        if(is_array($message))
        {
            $this->errors = $message;
            $message = "Field error";
        }
        parent::__construct($message, $code, $previous);
    }

    public function __toString() :string{
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    public function getErrors() :array{
        return $this->errors;
    }
}
?>