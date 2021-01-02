<?php

namespace PHPSimpleLib\Modules\JobManagement\Commands;

use PHPSimpleLib\Core\Controlling\CliController;
use PHPSimpleLib\Helper\URI;
use PHPSimpleLib\Modules\JobManagement\Lib\EnumJobHandlingResult;
use PHPSimpleLib\Modules\JobManagement\Lib\IJobWorker;
use PHPSimpleLib\Modules\JobManagement\Model\JobTypeModel;
use PHPSimpleLib\Modules\JobManagement\Service\JobConsumerService;
use PHPSimpleLib\Modules\JobManagement\Service\JobTypeService;

class WorkerCommandController extends CliController
{
    private const SLEEP_SECONDS_ON_ZERO_RESULTS = 60;

    private $currentJobList = array();
    private $currentJobIndex = -1;
    private $currentJobListCount = 0;
    private $currentJobTypeId = null;
    private $currentJobType = null;
    private $currentLimit = null;
    private $loopShouldRun = true;

    public function testAction() : void {
        var_dump(URI::parse('class://Abc/def/ghi'));
    }

    public function runAction(int $jobType, int $limit) : void {
        $this->prepareSigHandling();

        $this->currentJobTypeId = $jobType;
        $this->currentJobType = null;
        $this->currentLimit = $limit;
        $this->currentJobIndex = -1;
        $this->currentJobList = array();

        $this->currentJobType = JobTypeService::getJobTypeById($this->currentJobTypeId);
        if(!is_null($this->currentJobType)) {
            $this->outLine('Job worker running for job type "'.$this->currentJobType->getName().'", limited to '.$this->currentLimit.' jobs per run.');
            $this->loop($this->currentJobType, $this->currentLimit);
        } else {
            throw new \Exception('Invalid job type.');
        }
        exit;
    }

    private function loop(JobTypeModel $jobType, int $limit) : void {
        while($this->loopShouldRun) {
            $this->out('Searching for jobs ("'.$jobType->getName().'")... ');

            $this->currentJobList = JobConsumerService::getJobsForProcessingByType($jobType, $limit);
            $this->currentJobListCount = count($this->currentJobList);
            $this->currentJobIndex = -1;

            $this->outLine($this->currentJobListCount.' found');

            if($this->currentJobListCount === 0) {
                $this->outLine('No open jobs found, I\'ll sleep for a moment ('.self::SLEEP_SECONDS_ON_ZERO_RESULTS.' seconds)');
                sleep(self::SLEEP_SECONDS_ON_ZERO_RESULTS);
                continue;
            }

            for($i = 0; $i < $this->currentJobListCount; $i++) {
                $this->currentJobIndex = $i;
                try {
                    $currentJob = $this->currentJobList[$this->currentJobIndex]->getJob();
                    $currentJob = JobConsumerService::getJobForHandling($currentJob);
                    if(!$currentJob) {
                        continue;
                    }

                    switch($this->currentJobType->getMode()) {
                        case JobTypeModel::MODE_PHP_HANDLER:
                            $className = $this->currentJobType->getLocator();
                            $worker = new $className;
                            if($worker instanceof IJobWorker) {
                                try {
                                    $handlingResult = $worker->handleJob($currentJob);
                                    JobConsumerService::processJobHandlingResult($currentJob, $handlingResult->getResultCode(), $handlingResult->getMessage(), $handlingResult->getAdditionalData());
                                } catch (\Throwable $th) {
                                    JobConsumerService::processJobHandlingResult($currentJob, EnumJobHandlingResult::FAILED, $th->getMessage(), $th->getTrace());
                                }
                            }
                        break;
                        case JobTypeModel::MODE_PHP_HANDLER_INFINITE:
                            $className = $this->currentJobType->getLocator();
                            $worker = new $className;
                            if($worker instanceof IJobWorker) {
                                try {
                                    $handlingResult = $worker->handleJob($currentJob);
                                    JobConsumerService::processJobHandlingResult($currentJob, $handlingResult->getResultCode(), $handlingResult->getMessage(), $handlingResult->getAdditionalData());
                                } catch (\Throwable $th) {
                                    JobConsumerService::processJobHandlingResult($currentJob, EnumJobHandlingResult::FAILED, $th->getMessage(), $th->getTrace());
                                } finally {
                                    if(!$currentJob->isOpen() && !$currentJob->isProcessing()) {
                                        JobConsumerService::cloneToRestartJob($currentJob);
                                    }
                                }
                            }
                        break;
                        default:
                            throw new \Exception('Implementation of job type mode is missing.');
                    }
                } catch (\Throwable $th) {
                    $this->gracefullyShutdown();
                    $this->loopShouldRun = false;
                    throw $th;
                } finally {
                    
                }
            }
        }
    }

    private function gracefullyShutdown() : void {
        $this->out('Gracefully shutdown... ');
        if($this->currentJobIndex > -1) {
            $currentJob = $this->currentJobList[$this->currentJobIndex]->getJob();
            JobConsumerService::resetJobToOpenByGracefullyShutdown($currentJob);
        }
        $this->outLine('complete');
    }

    private function prepareSigHandling() : void {
        // declare(ticks = 1);
        pcntl_async_signals(true);
        pcntl_signal(SIGTERM, array($this, "handleSignal"));
        pcntl_signal(SIGHUP,  array($this, "handleSignal"));
        pcntl_signal(SIGUSR1, array($this, "handleSignal"));
    }

    public function handleSignal(int $signo, $signinfo) : void {
        switch ($signo) {
            case SIGTERM:
                $this->outLine('Received SIGTERM, shutting down gracefully...');
                $this->gracefullyShutdown();
                $this->loopShouldRun = false;
                //exit;
                break;
            case SIGHUP:
                $this->outLine('Received SIGHUP, restarting after shutting down gracefully...');
                $this->gracefullyShutdown();
                $this->loopShouldRun = false;
                $this->runAction($this->currentJobTypeId, $this->currentLimit);
                break;
            case SIGUSR1:
                $this->outLine("SIGUSR1...");
                break;
            default:
                // Alle anderen Signale bearbeiten
        }
    }
}
