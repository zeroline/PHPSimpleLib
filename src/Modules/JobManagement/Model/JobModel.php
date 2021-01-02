<?php

namespace PHPSimpleLib\Modules\JobManagement\Model;
;
use PHPSimpleLib\Core\Data\EnumValidatorRules;
use PHPSimpleLib\Modules\DataIntegrity\Model\DataIntegrityModel;

class JobModel extends DataIntegrityModel
{
    public const STATUS_OPEN = 0;
    public const STATUS_PROCESSING = 100;
    public const STATUS_FAILED = 400;
    public const STATUS_ERROR = 666;
    public const STATUS_FINISHED = 500;

    protected $tableName = "job";

    private $cachedType = null;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    protected $ignoreFieldsOnSerialization = array(
        
    );
    
    protected $fieldsForValidation = array(
        'type' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'payload' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
        'parameter' => array(
            EnumValidatorRules::REQUIRED => array(),
        ),
        'status' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
            EnumValidatorRules::IN_ARRAY => array(
                array(self::STATUS_OPEN, self::STATUS_PROCESSING, self::STATUS_ERROR, self::STATUS_FINISHED, self::STATUS_FAILED)
            ),
        ),
        'attempt' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
    );
    
    protected $fieldsForValidationScopes = array();

    public function getTypeId() : int {
        return $this->type;
    }

    public function getType() : JobTypeModel {
        if(is_null($this->cachedType)) {
            $this->cachedType = JobTypeModel::findOneById($this->getTypeId());
        }
        return $this->cachedType;
    }

    public function getStatus() : int {
        return $this->status;
    }

    public function getAttempt() : int {
        return $this->attempt;
    }

    public function setAttempt(int $attempt) : void {
        $this->attempt = $attempt;
    }

    public function incAttempt() : int {
        $this->attempt++;
        return $this->getAttempt();
    }

    public function isOpen() : bool {
        return $this->getStatus() === self::STATUS_OPEN;
    }

    public function isFinished() : bool {
        return $this->getStatus() === self::STATUS_FINISHED;
    }

    public function isFailed() : bool {
        return $this->getStatus() === self::STATUS_FAILED;
    }

    public function isProcessing() : bool {
        return $this->getStatus() === self::STATUS_PROCESSING;
    }

    public function hasAttemptsLeft() : bool {
        return ( $this->getAttempt() < $this->getType()->getMaxRetries() );
    }

    public function getPayload() : string {
        return $this->payload;
    }

    public function getPayloadArray() : array {
        return json_decode($this->getPayload());
    }

    public function getParameter() : string {
        return $this->parameter;
    }

    public function getParameterArray() : array {
        return json_decode($this->getParameter());
    }
}
