<?php

namespace PHPSimpleLib\Modules\JobManagement\Model;

;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class JobTypeModel extends DatabaseAbstractionModel
{
    public const MODE_PHP_HANDLER = 100;
    public const MODE_PHP_HANDLER_INFINITE = 101;

    public const DEFAULT_MAX_RETRIES = 5;
    public const DEFAULT_RETRY_DELAY = 10; // 10 seconds delay before trying again

    protected $tableName = "jobType";

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    protected $ignoreFieldsOnSerialization = array(

    );

    protected $fieldsForValidation = array(
        'name' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
        'mode' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
            EnumValidatorRules::IN_ARRAY => array(
                array(self::MODE_PHP_HANDLER, self::MODE_PHP_HANDLER_INFINITE)
            )
        ),
        'configuration' => array(
        ),
        'locator' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(16000),
        ),
        'retryDelay' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'maxRetries' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        )
    );

    protected $fieldsForValidationScopes = array();

    public function getConfiguration(): string
    {
        return $this->configuration;
    }

    public function getConfigurationObject()
    {
        return json_decode($this->getConfiguration());
    }

    public function setConfiguration($config): void
    {
        if (!is_array($config) || !is_object($config)) {
            throw new \Exception('Invalid format');
        }
        $this->configuration = json_encode($config);
    }

    public function getRetryDelay(): ?int
    {
        return $this->retryDelay;
    }

    public function getMaxRetries(): ?int
    {
        return $this->maxRetries;
    }

    public function getLocator(): string
    {
        return $this->locator;
    }

    public function getMode(): int
    {
        return $this->mode;
    }

    public function getName(): string
    {
        return $this->name;
    }
}
