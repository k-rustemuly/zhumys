<?php

namespace App\Helper;

use App\Factory\ValidationFactory;
use Cake\Validation\Validator as Val;

/**
 * Service.
 */ 
class Validator
{

    /**
     * @var ValidationFactory
     */
    public $validationFactory;

    /**
     * @var Val
     */
    public $validator;

    /**
     * @var array
     */
    public $config;

    /**
     * The constructor.
     *
     * @param ValidationFactory $validationFactory The validation
     */
    public function __construct(){
        $this->validationFactory = new ValidationFactory();
    }
    
    /**
     * Set the configuration for the validation
     *
     * @param array<mixed> $data The data
     *
     * @return self
     */
    public function setConfig(array $config) :self{
        $this->config = $config;
        return $this;
    }
    

    /**
     * Validate user profile information for updating
     *
     * @param array<mixed> $data The data
     *
     * @return array<mixed> $data The data
     */
    public function validateOnCreate(array $data) :array{
        $this->validator = $this->validationFactory->createValidator($this->config);
        return $this->validationFactory->validate($this->validator, $data);
    }

    /**
     * Validate information for updating
     *
     * @param array<mixed> $data The data
     *
     * @return array<mixed> $data The data
     */
    public function validateOnUpdate(array $data) :array{
        $this->validator = $this->validationFactory->createValidator($this->config);
        return $this->validationFactory->validate($this->validator, $data, false);
    }

}
