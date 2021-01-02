<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class ServiceAccessValuesModel extends DatabaseAbstractionModel
{
    public const TABLE_NAME = "serviceaccessvalues";
    protected $tableName = self::TABLE_NAME;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    protected $ignoreFieldsOnSerialization = array(

    );

    protected $fieldsForValidation = array(
        'serviceaccessid' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'name' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
    );

    protected $fieldsForValidationScopes = array();

    /**
     * Returns the serviceaccessid
     *
     * @return string
     */
    public function getServiceAccessId(): int
    {
        return $this->serviceaccessid;
    }

    /**
     * Returns the parent service access
     *
     * @return ServiceAccessModel
     */
    public function getServiceAccess(): ServiceAccessModel
    {
        return ServiceAccessModel::findOneById($this->getServiceAccessId());
    }

    /**
     * Returns the name
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Returns the content
     *
     * @return string
     */
    public function getContent(): string
    {
        return $this->content;
    }
}
