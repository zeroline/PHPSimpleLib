<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Communication\Rest\AsyncCurl;

use PHPSimpleLib\Core\Communication\Rest\Curly as Curly;
use PHPSimpleLib\Core\Communication\Rest\AsyncCurl\CurlJob as CurlJob;

class MultiCurly
{
    /**
     *
     * @var array
     */
    private static $jobs = array();
    
    /**
     * Clears all jobs
     */
    public static function clearJobs()
    {
        static::$jobs = array();
    }
    
    /**
     *
     * @param CurlJob $job
     */
    public static function addJob(CurlJob $job)
    {
        static::$jobs[] = $job;
    }
    
    /**
     *
     * @return array
     */
    public static function getJobs()
    {
        return static::$jobs;
    }
    
    /**
     * Runs the given jobs and returns them in an array. The results will be within
     * the CurlJob object.
     *
     * @return array
     */
    public static function run()
    {
        // Multihandle
        $mh = curl_multi_init();
        
        // Curlhandle
        $ch = array();
        
        // Jobs
        $jobs = static::getJobs();
        
        for ($i = 0; $i < count($jobs); $i++) {
            $job = $jobs[$i];
            if ($job instanceof CurlJob) {
                $ch[$i] = Curly::init($job->getUrl(), $job->getMethod(), $job->getParameter());
                curl_multi_add_handle($mh, $ch[$i]);
            }
        }
        
        $execReturnValue = null;
        $runningHandles = 0;
        
        do {
            $execReturnValue = curl_multi_exec($mh, $runningHandles);
        } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
        
        // Loop and continue processing the request
        while ($runningHandles && ($execReturnValue == CURLM_OK || $execReturnValue == CURLM_CALL_MULTI_PERFORM)) {
            usleep(1);
            // Wait forever for network
            $numberReady = curl_multi_select($mh);
            if ($numberReady == -1) {
                usleep(100000);
            }
            
            if ($execReturnValue >= CURLM_CALL_MULTI_PERFORM) {
                do {
                    $execReturnValue = curl_multi_exec($mh, $runningHandles);
                    usleep(1);
                } while ($execReturnValue == CURLM_CALL_MULTI_PERFORM);
            }
        }
        
        // Check for any errors
        if ($execReturnValue != CURLM_OK) {
            trigger_error("Curl multi read error $execReturnValue\n", E_USER_WARNING);
        }
        
        // Extract the content
        for ($i = 0; $i < count($jobs); $i++) {
            $curlError = curl_error($ch[$i]);
            if (empty($curlError)) {
                $jobs[$i]->setResult(curl_multi_getcontent($ch[$i]));
            } else {
                $jobs[$i]->setResult(false);
            }
            curl_multi_remove_handle($mh, $ch[$i]);
            curl_close($ch[$i]);
        }
        
        curl_multi_close($mh);

        return $jobs;
    }
}
