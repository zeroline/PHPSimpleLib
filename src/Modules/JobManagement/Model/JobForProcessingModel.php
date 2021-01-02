<?php

namespace PHPSimpleLib\Modules\JobManagement\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Modules\JobManagement\Service\JobConsumerService;

class JobForProcessingModel extends DatabaseAbstractionModel
{
    protected $tableName = "vJobsForProcessing";

    public function __construct($data = null)
    {
        parent::__construct($data);
    }

    public function getJob(): JobModel
    {
        return JobConsumerService::getJobById($this->jobId);
    }
}
