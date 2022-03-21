<?php

namespace App\Factory;

use Cake\Validation\Validator;
use Selective\Validation\Converter\CakeValidationConverter;
use Selective\Validation\ValidationResult;
use App\Exception\FieldException;
use DomainException;

/**
 * Validation factory.
 */
final class ValidationFactory {

    /**
     * @var array
     */
    private $config;

    /**
     * Create validator.
     * @param array<mixed> $config The config data
     *
     * @return Validator The validator
     */
    public function createValidator(array $config): Validator{
        $this->config = $config;
        $validator = new Validator();
        foreach($config as $field => $properties) {
            $type = $properties["type"];
            $is_required = $properties["is_required"];
            $can_update = $properties["can_update"];
            $can_create = $properties["can_create"];
            if($type == "text") {
                if($can_create) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "Field is required");
                    }
                }
                if($can_update) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "Field is required");
                    }
                }
            } 
            else if($type == "email") {
                if($can_create) {
                    if($is_required) {
                        $validator->email($field, true, "Email is invalid", Validator::WHEN_CREATE);
                    }
                }
                if($can_update) {
                    if($is_required) {
                        $validator->email($field, true, "Email is invalid", Validator::WHEN_UPDATE);
                    }
                }
            }
            else if($type == "date") {
                if($can_create) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "Select the date");
                        $validator->date($field, ["ymd"], "Select the date", Validator::WHEN_CREATE);
                    }
                }
                if($can_update) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "Select the date");
                        $validator->date($field, ["ymd"], "Select the date", Validator::WHEN_UPDATE);
                    }
                }
            }
            else if($type == "reference") {
                if($can_create) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "Select one from list");
                    }
                }
                if($can_update) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "Select one from list");
                    }
                }
            }
            else if($type == "tag") {   
                if($can_create) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "Select at least one");
                        $validator->notEmptyArray($field, "Field is required", Validator::WHEN_CREATE);
                    }
                }
                if($can_update) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "Select at least one");
                        $validator->notEmptyArray($field, "Field is required", Validator::WHEN_UPDATE);
                    }
                }
            }
            else if($type == "base64") {
                if($can_create) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "Select file");
                    }
                }
                if($can_update) {
                    $validator->notEmptyString($field, "Field is empty", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "Select file");
                    }
                }
            }
            else if($type == "number") {
                $min_length = (int) $properties["min_length"];
                $max_length = (int) $properties["max_length"];
                if($min_length > 0) {
                    $when = null;
                    if($can_create) {
                        $when = Validator::WHEN_CREATE;
                    }
                    else if ($can_update) {
                        $when = Validator::WHEN_UPDATE;
                    }
                    if($when != null) {
                        $validator->minLength($field, $min_length, "The length of is not accepted", $when);
                    }
                }
                if($max_length > 0) {
                    $when = null;
                    if($can_create) {
                        $when = Validator::WHEN_CREATE;
                    }
                    else if ($can_update) {
                        $when = Validator::WHEN_UPDATE;
                    }
                    if($when != null) {
                        $validator->maxLength($field, $max_length, "The length of is not accepted", $when);
                    }
                }
                if($can_create) {
                    $validator->naturalNumber($field, "The number is not natural", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "The number is required");
                    }
                }
                if($can_update) {
                    $validator->naturalNumber($field, "The number is not natural", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "The number is required");
                    }
                }
            }
            else if($type == "boolean") {
                if($can_create) {
                    $validator->boolean($field, true, "Is not boolean", Validator::WHEN_CREATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_CREATE, "The boolean is required");
                    }
                }
                if($can_update) {
                    $validator->boolean($field, true, "Is not boolean", Validator::WHEN_UPDATE);
                    if($is_required) {
                        $validator->requirePresence($field, Validator::WHEN_UPDATE, "The boolean is required");
                    }
                }
            }
        }
        return $validator;
    }

    /**
     * Create validation result from array with errors.
     *
     * @param array $errors The errors
     *
     * @return ValidationResult The result
     */
    public function createResultFromErrors(array $errors): ValidationResult{
        return CakeValidationConverter::createValidationResult($errors);
    }

    /**
     * Check
     *
     * @param Validator $validator The errors
     * @param array<mixed> $data The errors
     * @param bool $newRecord whether the data to be validated is new or to be updated.
     * 
     * @throws FieldException
     * 
     * @return array<mixed> The filtered data
     *
     */
    public function validate(Validator $validator, array $data, bool $newRecord = true) :array{
        $validationResult = $this->createResultFromErrors(
            $validator->validate($data, $newRecord)
        );
        if ($validationResult->fails()) {
            $message = array();
            $errors = $validationResult->getErrors();
            foreach($errors as $error) {
                throw new DomainException($error->getField()." ".$error->getMessage());
                $message[] = array("field" => $error->getField(), "message" => $error->getMessage());
            }
            throw new FieldException($message);
        }
        foreach($data as $key => $value) {
            if($newRecord && !isset($this->config[$key])) unset($data[$key]);
            if(!$newRecord && (!isset($this->config[$key]) || !$this->config[$key]["can_update"])) unset($data[$key]);
        }
        return $data;
    }
}
