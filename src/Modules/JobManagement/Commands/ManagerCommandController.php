<?php

namespace PHPSimpleLib\Modules\JobManagement\Commands;

use PHPSimpleLib\Core\Controlling\CliController;
use PHPSimpleLib\Helper\URI;
use PHPSimpleLib\Modules\JobManagement\Lib\EnumJobHandlingResult;
use PHPSimpleLib\Modules\JobManagement\Lib\IJobWorker;
use PHPSimpleLib\Modules\JobManagement\Model\JobModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobTypeModel;
use PHPSimpleLib\Modules\JobManagement\Service\JobConsumerService;
use PHPSimpleLib\Modules\JobManagement\Service\JobTypeService;

class ManagerCommandController extends CliController
{
    private const LINE = '================================';

    private function getJobStatusString(int $status): string
    {
        switch ($status) {
            case JobModel::STATUS_OPEN:
                return 'Open';
            case JobModel::STATUS_PROCESSING:
                return 'Processing';
            case JobModel::STATUS_FINISHED:
                return 'Finished';
            case JobModel::STATUS_FAILED:
                return 'Failed';
            case JobModel::STATUS_ERROR:
                return 'Error';
            default:
                return 'Unknown';
        }
    }

    public function listAction(int $jobTypeId, int $limit = 10): void
    {
        $jobType = JobTypeService::getJobTypeById($jobTypeId);
        if ($jobType) {
            $jobsForProcessing = JobConsumerService::getJobsForProcessingByType($jobType, $limit);
            foreach ($jobsForProcessing as $jobForProcessing) {
                $job = $jobForProcessing->getJob();
                $this->outLine(self::LINE);
                $this->outLine($jobType->getName() . ' (' . $this->getJobStatusString($job->getStatus()) . ')');
                $this->outLine($job->payload);
                $this->outLine(self::LINE . PHP_EOL);
            }
        } else {
            $this->outLine('Invalid job type');
        }
    }

    public function listTypes(): void
    {
    }
}
