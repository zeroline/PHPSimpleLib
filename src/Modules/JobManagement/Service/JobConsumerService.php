<?php

namespace PHPSimpleLib\Modules\JobManagement\Service;

use PHPSimpleLib\Modules\JobManagement\Lib\EnumJobHandlingResult;
use PHPSimpleLib\Modules\JobManagement\Model\JobForProcessingModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobHistoryModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobModel;
use PHPSimpleLib\Modules\JobManagement\Model\JobTypeModel;

final class JobConsumerService {
    /**
     * Finds on job model by its id
     *
     * @param integer $id
     * @return JobModel
     */
    public static function getJobById(int $id): JobModel {
        return JobModel::findOneById($id);
    }

    /**
     * Returns an array of @see JobForProcessingModel
     *
     * @param JobTypeModel $type
     * @param integer $limit
     * @return array
     */
    public static function getJobsForProcessingByType(JobTypeModel $type, int $limit) : array {
        return JobForProcessingModel::repository()->where('jobType', $type->getId())->limit($limit)->read();
    }

    /**
     * Returns the given job after setting the status to processing and
     * increasing the attempt counter by one
     *
     * @param JobModel $job
     * @return JobModel|null
     */
    public static function getJobForHandling(JobModel $job) : ?JobModel {
        if($job->isOpen() && $job->hasAttemptsLeft()) {
            $job->status = JobModel::STATUS_PROCESSING;
            $job->incAttempt();
            $job->save();

            JobHistoryService::log($job, JobHistoryService::MESSAGE_RETURNED_FOR_HANDLING, null);

            return $job;
        }

        return null;
    }

    /**
     * Use this only if the processing is interupted.
     * It reduces the attempt counter by one and sets the job to open.
     *
     * @param JobModel $jobModel
     * @return void
     */
    public static function resetJobToOpenByGracefullyShutdown(JobModel $jobModel) : void {
        $jobModel->attempt = $jobModel->attempt - 1;
        $jobModel->status = JobModel::STATUS_OPEN;
        $jobModel->save();
    }

    /**
     * Creates a new job with the data of the given job
     *
     * @param JobModel $jobModel
     * @return void
     */
    public static function cloneToRestartJob(JobModel $jobModel) : void {
        JobProducerService::addJob($jobModel->getType(), $jobModel->getPayloadArray(), $jobModel->getParameterArray());
    }

    /**
     * Processes the handlers result after processing the job
     *
     * @param JobModel $jobModel
     * @param integer $result
     * @param string|null $message
     * @param mixed $additionalData
     * @return void
     */
    public static function processJobHandlingResult(JobModel $jobModel, int $result, ?string $message = null, $additionalData = null) : void {
        switch($result) {
            case EnumJobHandlingResult::SUCCESS: 
                $jobModel->status = JobModel::STATUS_FINISHED;
                $jobModel->save();
                JobHistoryService::log($jobModel, JobHistoryService::MESSAGE_SUCCESS.($message ? ' : '.$message : ''), $additionalData);
            break;
            case EnumJobHandlingResult::FAILED:
                if($jobModel->hasAttemptsLeft()) {
                    $jobModel->status = JobModel::STATUS_OPEN;
                    JobHistoryService::log($jobModel, JobHistoryService::MESSAGE_FAILED_BUT_RETRY.($message ? ' : '.$message : ''), $additionalData);
                } else {
                    $jobModel->status = JobModel::STATUS_FAILED;
                    JobHistoryService::log($jobModel, JobHistoryService::MESSAGE_FAILED.($message ? ' : '.$message : ''), $additionalData);
                }
                $jobModel->save();
            break;
            case EnumJobHandlingResult::ERROR:
                $jobModel->status = JobModel::STATUS_ERROR;
                $jobModel->save();
                JobHistoryService::log($jobModel, JobHistoryService::MESSAGE_ERROR.($message ? ' : '.$message : ''), $additionalData);
            break;
            default:
                throw new \Exception('Invalid job handling result');
        }
    }
}