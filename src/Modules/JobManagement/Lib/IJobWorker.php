<?php

namespace PHPSimpleLib\Modules\JobManagement\Lib;

use PHPSimpleLib\Modules\JobManagement\Model\JobModel;

interface IJobWorker
{
    public function handleJob(JobModel $job): JobHandlingResult;
}
