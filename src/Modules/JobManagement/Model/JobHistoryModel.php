<?php

namespace PHPSimpleLib\Modules\JobManagement\Model;

;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;
use PHPSimpleLib\Modules\JobManagement\Service\JobConsumerService;

class JobHistoryModel extends DatabaseAbstractionModel
{
    protected $tableName = "jobHistory";

    private $cachedJob = null;

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    protected $ignoreFieldsOnSerialization = array(

    );

    protected $fieldsForValidation = array(
        'jobId' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
    );

    protected $fieldsForValidationScopes = array();

    public function getJobId(): int
    {
        return $this->jobId;
    }

    public function getJob(): JobModel
    {
        if (is_null($this->cachedJob)) {
            $this->cachedJob = JobConsumerService::getJobById($this->getJobId());
        }
        return $this->cachedJob;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getAdditionalData(): string
    {
        return $this->additionalData;
    }

    public function getCreated()
    {
        return $this->created;
    }
}
