<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\Model;

class ValidatedModel extends Model
{
    use ValidatorTrait;
    
    const VALIDATION_SCOPE_CREATE = 'create';
    const VALIDATION_SCOPE_UPDATE = 'update';

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function jsonSerialize()
    {
        $data = parent::jsonSerialize();
        // Perform filter 
        $this->filter();
        
        return $data;
    }
}
