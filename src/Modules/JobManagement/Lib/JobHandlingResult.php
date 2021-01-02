<?php

namespace PHPSimpleLib\Modules\JobManagement\Lib;

use PHPSimpleLib\Modules\JobManagement\Model\JobModel;

class JobHandlingResult
{
    private $resultCode = null;
    private $job = null;
    private $message = null;
    private $additionalData = null;

    public function __construct(JobModel $jobModel, int $resultCode, ?string $message, $additionalData = null)
    {
        $this->job = $jobModel;
        $this->resultCode = $resultCode;
        $this->message = $message;
        $this->additionalData = $additionalData;
    }

    public function getJob(): JobModel
    {
        return $this->job;
    }

    public function getResultCode(): int
    {
        return $this->resultCode;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getAdditionalData()
    {
        return $this->additionalData;
    }
}
