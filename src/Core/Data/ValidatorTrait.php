<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\Validator;
use PHPSimpleLib\Core\Data\EnumValidatorRules;
use PHPSimpleLib\Core\Data\EnumFilterModes;

trait ValidatorTrait
{
    /**
     *
     * @var array
     */
    protected $fieldsForValidation = array();
    
    /**
     *
     * @var array
     */
    protected $fieldsForValidationScopes = array();

    /**
     *
     * @var array
     */
    protected $fieldValidationErrors = array();

    /**
     * @see \PHPSimpleLib\Core\Data\EnumFilterModes
     *
     * @var string
     */
    protected $filterMode = EnumFilterModes::FILTER_MODE_BOTH;

    /**
     * Sets the current filter mode.
     *
     * @param string $filterMode @see \PHPSimpleLib\Core\Data\EnumFilterModes
     * @return void
     */
    protected function setFilterMode(string $filterMode) : void {
        if(in_array($filterMode, [EnumFilterModes::FILTER_MODE_AFTER_AFTER_FETCH, EnumFilterModes::FILTER_MODE_BEFORE_SAVE, EnumFilterModes::FILTER_MODE_BOTH])) {
            $this->filterMode = $filterMode;
        } else {
            throw new \Exception('Invalid filter mode.');
        }
    }

    /**
     * Returns the current filter mode
     *
     * @return string
     */
    protected function getFilterMode() : string {
        return $this->filterMode;
    }

    /**
     * Sets the validation fields
     *
     * @param array $fieldsForValidation
     * @return void
     */
    public function setFieldsForValidation(array $fieldsForValidation) : void
    {
        $this->fieldsForValidation = $fieldsForValidation;
    }

    /**
     *
     * @return array
     */
    public function getErrors()
    {
            return $this->fieldValidationErrors;
    }
    
    /**
     *
     * @param string $scopeName
     * @return boolean
     */
    public function hasScope(string $scopeName) : bool
    {
        return (bool) array_key_exists($scopeName, $this->fieldsForValidationScopes);
    }
    
    /**
     *
     * @param string $scope
     * @return boolean
     * @throws \RuntimeException
     */
    public function isValid($scope = null)
    {
        $this->fieldValidationErrors = array();
        $fields = (is_null($scope) ? $this->fieldsForValidation : array_merge($this->fieldsForValidation, $this->fieldsForValidationScopes[$scope]));
        if (sizeof($fields) === 0) {
            return true;
        }

        $valid = true;

        foreach ($fields as $name => $rules) {
            $value = (isset($this->{$name}) ? $this->{$name} : null);

            foreach ($rules as $rule => $arguments) {
                $result = null;
                if (is_string($rule)) {
                    if (!isset($value) && $rule != EnumValidatorRules::REQUIRED) {
                        continue;
                    }

                    if(in_array($rule, array(EnumValidatorRules::FILTER_ENCODE_HTML, EnumValidatorRules::FILTER_STRIP_HTML))) {
                        continue;
                    }

                    if ($rule == EnumValidatorRules::CUSTOM && is_callable($arguments)) {
                        $f = $arguments;
                        $result = $f($value, $this);
                    } elseif (method_exists(Validator::class, $rule)) {
                        $arguments = array_merge(array($value), $arguments);
                        $result = forward_static_call_array(array(Validator::class,$rule), $arguments);
                    } elseif (method_exists($this, $rule)) {
                        $arguments = array_merge(array($value), $arguments);
                        $result = call_user_func_array(array($this,$rule), $arguments);
                    } else {
                        throw new \RuntimeException('Validation rule method "' . $rule . '" cannot be found.');
                    }
                }

                if ($result !== true) {
                    $valid = false;
                    $this->fieldValidationErrors[] = array('field' => $name, 'rule' => $rule, 'ruleResult' => $result);
                }
            }
        }
        return $valid;
    }

    /**
     * Perform filter actions for configured fields.
     * Filter action may change the fields value.
     * Prefered use before handing data to a client
     *
     * @return void
     */
    public function filter() : void {
        $fields = $this->fieldsForValidation;
        foreach ($fields as $name => $rules) {
            $value = (isset($this->{$name}) ? $this->{$name} : null);
            foreach ($rules as $rule => $arguments) {
                if(in_array($rule, array(EnumValidatorRules::FILTER_ENCODE_HTML, EnumValidatorRules::FILTER_STRIP_HTML))) {
                    if (method_exists(Validator::class, $rule)) {
                        $arguments = array_merge(array($value), $arguments);
                        $this->{$name} = forward_static_call_array(array(Validator::class,$rule), $arguments);
                    }
                }
            }
        }
    }
}
