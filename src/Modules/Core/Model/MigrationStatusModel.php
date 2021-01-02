<?php

namespace PHPSimpleLib\Modules\Core\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class MigrationStatusModel extends DatabaseAbstractionModel
{
    public const TABLE_NAME = "migrationstatus";
    protected $tableName = self::TABLE_NAME;

    protected $ignoreFieldsOnSerialization = array(

    );
    
    protected $fieldsForValidation = array(
        'moduleName' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
        'migrationFile' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
        'migrationDate' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
        'migrationData' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
    );
    
    protected $fieldsForValidationScopes = array();

    /**
     * Returns the module name
     *
     * @return string
     */
    public function getModuleName() : string
    {
        return $this->moduleName;
    }

    /**
     * Returns the executed migration file name
     *
     * @return string
     */
    public function getMigrationFile() : string
    {
        return $this->migrationFile;
    }

    /**
     * Returns the migration date
     *
     * @return string
     */
    public function getMigrationDate() : string
    {
        return $this->migrationDate;
    }

    /**
     * Returns the migration file contents
     *
     * @return string
     */
    public function getMigrationData() : string
    {
        return $this->migrationData;
    }
}
