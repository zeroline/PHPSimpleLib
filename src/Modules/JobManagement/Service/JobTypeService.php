<?php

namespace PHPSimpleLib\Modules\JobManagement\Service;

use PHPSimpleLib\Modules\JobManagement\Model\JobTypeModel;

final class JobTypeService {
    public static function addJobType(string $name, int $mode, string $locator, int $maxRetries = JobTypeModel::DEFAULT_MAX_RETRIES, int $retryDelay = JobTypeModel::DEFAULT_RETRY_DELAY,  ?string $description, ?array $configuration) : ?JobTypeModel {
        $model = new JobTypeModel([
            'name' => $name,
            'mode' => $mode,
            'locator' => $locator,
            'description' => $description,
            'configuration' => json_encode($configuration),
            'maxRetries' => $maxRetries,
            'retryDelay' => $retryDelay
        ]);

        if($model->validateAndSave()) {
            return $model;
        }
        return null;
    }

    /**
     * Find one jobType by it's id
     *
     * @param integer $id
     * @return JobTypeModel|null
     */
    public static function getJobTypeById(int $id) : ?JobTypeModel {
        return JobTypeModel::findOneById($id);
    }

    /**
     * Find one jobType by its name
     *
     * @param string $name
     * @return JobTypeModel|null
     */
    public static function getJobTypeByName(string $name): ?JobTypeModel {
        return JobTypeModel::repository()->where('name', $name)->readOne();
    }
}