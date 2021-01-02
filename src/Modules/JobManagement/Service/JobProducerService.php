<?php

namespace PHPSimpleLib\Modules\JobManagement\Service;

use PHPSimpleLib\Modules\DataIntegrity\Lib\EnumEntryState;
use PHPSimpleLib\Modules\JobManagement\Model\JobModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobTypeModel;

final class JobProducerService
{

    /**
     * Adds a new job to the job queue.
     * Required is a valid job type and some payload
     *
     * @param JobTypeModel $jobType
     * @param array $payload
     * @param array $parameter
     * @return JobModel|null
     */
    public static function addJob(JobTypeModel $jobType, array $payload = [], array $parameter = []): ?JobModel
    {
        $model = new JobModel([
            'type' => $jobType->getId(),
            'payload' => json_encode($payload),
            'parameter' => json_encode($parameter),
            'status' => JobModel::STATUS_OPEN,
            'attempt' => 0,
            'activeState' => EnumEntryState::ACTIVE
        ]);

        if ($model->validateAndSave()) {
            JobHistoryService::log($model, JobHistoryService::MESSAGE_CREATED, null);
            return $model;
        }
        return null;
    }
}
