<?php

namespace PHPSimpleLib\Modules\JobManagement\Service;

use PHPSimpleLib\Modules\JobManagement\Model\JobHistoryModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobModel;

final class JobHistoryService
{
    public const MESSAGE_CREATED = "The job has been created";
    public const MESSAGE_RETURNED_FOR_HANDLING = "The job has been selected for handling";
    public const MESSAGE_SUCCESS = 'Job completed successfully';
    public const MESSAGE_FAILED = 'Job failed';
    public const MESSAGE_FAILED_BUT_RETRY = 'Job failed, retrys left';
    public const MESSAGE_ERROR = 'Job raised an error';

    /**
     * Log data for the given job
     *
     * @param JobModel $job
     * @param string $message
     * @param mixed $additionalData
     * @return JobHistoryModel|null
     */
    public static function log(JobModel $job, string $message, $additionalData): ?JobHistoryModel
    {
        $model = new JobHistoryModel([
            'jobId' => $job->getId(),
            'message' => $message,
            'additionalData' => is_string($additionalData) ? $additionalData : json_encode($additionalData),
            'created' => date('Y-m-d H:i:s')
        ]);

        if ($model->validateAndSave()) {
            return $model;
        }
        return null;
    }
}
