<?php

namespace App\Factory;

use Cake\Validation\Validator;
use Selective\Validation\Converter\CakeValidationConverter;
use Selective\Validation\ValidationResult;
use App\Exception\FieldException;

/**
 * Validation factory.
 */
final class ValidationFactory
{

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
    public function createValidator(array $config): Validator
    {
        $this->config = $config;
        $validator = new Validator();
        foreach($config as $field => $properties)
        {
            $type = $properties["type"];
            $is_required = $properties["required"];
            $is_can_change = $properties["can_change"];
            if($type == "text")
            {
                $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_CREATE);
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'Field is required');

                }
                if($is_can_change)
                {
                    $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_UPDATE);
                }
            }
            else if($type == "date")
            {
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'Select the date');
                    $validator->date($field, ['ymd'], 'Select the date', Validator::WHEN_CREATE);
                }
                if($is_can_change)
                {
                    $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_UPDATE);
                }
            }
            else if($type == "reference")
            {
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'Select one from list');
                }
            }
            else if($type == "tag")
            {   
                $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_CREATE);
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'Select at least one');
                }
                if($is_can_change)
                {
                    $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_UPDATE);
                }
            }
            else if($type == "base64")
            {
                $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_CREATE);
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'Select file');
                }
                if($is_can_change)
                {
                    $validator->notEmptyString($field, 'Field is empty', Validator::WHEN_UPDATE);
                }
            }
            else if($type == "number")
            {
                $min_length = (int) $properties["min_length"];
                $max_length = (int) $properties["max_length"];
                if($min_length > 0){
                    $when = null;
                    if($is_required)
                        $when = Validator::WHEN_CREATE;
                    else if ($is_can_change)
                        $when = Validator::WHEN_UPDATE;
                    if($when != null)
                        $validator->minLength($field, $min_length, 'The length of is not accepted', $when);
                }
                if($max_length > 0){
                    $when = null;
                    if($is_required)
                        $when = Validator::WHEN_CREATE;
                    else if ($is_can_change)
                        $when = Validator::WHEN_UPDATE;
                    if($when != null)
                        $validator->maxLength($field, $max_length, 'The length of is not accepted', $when);
                }

                $validator->naturalNumber($field, 'The number is not natural', Validator::WHEN_CREATE);
                if($is_required)
                {
                    $validator->requirePresence($field, Validator::WHEN_CREATE, 'The number is required');
                }
                if($is_can_change)
                {
                    $validator->naturalNumber($field, 'The number is not natural', Validator::WHEN_UPDATE);
                }
            }
            else if($type == "boolean")
            {
                if($is_required)
                {
                    $validator->boolean($field, true, 'Is not boolean', Validator::WHEN_CREATE);
                }
                if($is_can_change)
                {
                    $validator->boolean($field, true, 'Is not boolean', Validator::WHEN_UPDATE);
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
    public function createResultFromErrors(array $errors): ValidationResult
    {
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
            foreach($errors as $error)
            {
                $message[] = array('field' => $error->getField(), 'message' => $error->getMessage());
            }
            throw new FieldException($message);
        }
        foreach($data as $key => $value){
            if($newRecord && !isset($this->config[$key])) unset($data[$key]);
            if(!$newRecord && (!isset($this->config[$key]) || !$this->config[$key]["can_change"])) unset($data[$key]);
        }
        return $data;
    }
}
