<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

class ModelPersistResult
{
    
    /**
     *
     * @var bool
     */
    private $valid = false;
    
    /**
     *
     * @var array
     */
    private $errors = array();
    
    /**
     *
     * @var \PHPSimpleLib\Core\Data\Model
     */
    private $model = null;
    
    public function __construct(bool $valid, $model = null, array $errors = array())
    {
        $this->valid = $valid;
        $this->model = $model;
        $this->errors = $errors;
    }
    
    /**
     *
     * @return bool
     */
    public function getIsValid() : bool
    {
        return $this->valid;
    }
    
    /**
     *
     * @return \PHPSimpleLib\Core\Data\Model
     */
    public function getModel() : \PHPSimpleLib\Core\Data\Model
    {
        return $this->model;
    }
    
    /**
     *
     * @return array
     */
    public function getErrors() : array
    {
        return $this->errors;
    }

    /**
     * Returns a normal array with
     * isValid, model, errors
     *
     * @return array
     */
    public function asArray() : array
    {
        return array($this->getIsValid(), $this->getModel(), $this->getErrors());
    }
}
