<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class ServiceAccessModel extends DatabaseAbstractionModel
{
    public const TABLE_NAME = "serviceaccess";
    protected $tableName = self::TABLE_NAME;

    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->addAutomaticField('updated', function ($model) {
            $model->updated = date("Y-m-d H:i:s");
        });
        $this->addAutomaticField('created', function ($model) {
            if ($model->isNew()) {
                $model->created = date("Y-m-d H:i:s");
            }
        });
    }

    protected $ignoreFieldsOnSerialization = array(
        'appSecret',
    );

    protected $fieldsForValidation = array(
        'name' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
        'appKey' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_LEN => array(64, 64),
        ),
        'appSecret' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_LEN => array(64, 64),
        ),
        'active' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IN_ARRAY => array(array(1,0)),
        ),
        'updated' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
        'created' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
    );

    protected $fieldsForValidationScopes = array();

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
     * Returns the description
     *
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * Returns the application access identifier
     *
     * @return string
     */
    public function getAppKey(): string
    {
        return $this->appKey;
    }

    /**
     * Returns the secret
     *
     * @return string
     */
    public function getAppSecret(): string
    {
        return $this->appSecret;
    }

    /**
     * Returns the updated datetime
     *
     * @return string
     */
    public function getUpdated(): string
    {
        return $this->updated;
    }

    /**
     * Returns the created datetime
     *
     * @return string
     */
    public function getCreated(): string
    {
        return $this->updated;
    }

    /**
     * Returns the active state
     *
     * @return int
     */
    public function getActive(): int
    {
        return $this->active;
    }

    /**
     * Returns an array of joined key values
     *
     * @return array
     */
    public function getValues(): array
    {
        return ServiceAccessValuesModel::repository()->where('serviceaccessid', $this->getId())->read();
    }
}
