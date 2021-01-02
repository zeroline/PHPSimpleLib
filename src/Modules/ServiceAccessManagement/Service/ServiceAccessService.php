<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Service;

use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessModel;
use PHPSimpleLib\Core\Data\ModelPersistResult;

final class ServiceAccessService
{
    /**
     * Creates a new client entry. Generates appKey and appSecret automaticly
     *
     * @param string $name
     * @param string $description
     * @return ModelPersistResult
     */
    public static function addClient(string $name, string $description = "") : ModelPersistResult
    {
        $model = new ServiceAccessModel(array(
            'name' => $name,
            'description' => $description,
            'appKey' => hash('sha256', $name . time()),
            'appSecret' => hash('sha256', mt_rand() . $name . time() . mt_rand()),
            'active' => 1,
            'updated' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s')
        ));

        return static::saveModel($model);
    }

    /**
     * Tries to find an active access entry by the given app key
     *
     * @param string|null $key
     * @return boolean
     */
    public static function hasClientAccessByAppKey(?string $key) : bool
    {
        if (!is_null($key) && !empty($key)) {
            $serviceAccessModel = self::findClientByAppKey($key);
            if ($serviceAccessModel) {
                return ($serviceAccessModel->getActive() == 1);
            }
        }
        return false;
    }

    /**
     * Checks if the access entry is active
     *
     * @param ServiceAccessModel|null $serviceAccess
     * @return boolean
     */
    public static function hasClientAccess(?ServiceAccessModel $serviceAccess) : bool
    {
        if (!is_null($serviceAccess)) {
            return ($serviceAccess->getActive() == 1);
        }
        return false;
    }

    /**
     * Finds one client access entry
     *
     * @param integer $id
     * @return ServiceAccessModel|null
     */
    public static function findClientById(int $id) : ?ServiceAccessModel
    {
        return ServiceAccessModel::findOneById($id);
    }

    /**
     * Returns one client access entry if found by key
     *
     * @param string $key
     * @return ServiceAccessModel|null
     */
    public static function findClientByAppKey(string $key) : ?ServiceAccessModel
    {
        return ServiceAccessModel::repository()->where('appKey', $key)->readOne();
    }

    /**
     * Returns all client entries
     *
     * @return array
     */
    public static function getAllClients() : array
    {
        return ServiceAccessModel::repository()->orderByAsc('name')->read();
    }

    /**
     * Removes one client access entry
     *
     * @param ServiceAccessModel $model
     * @return boolean
     */
    public static function removeClient(ServiceAccessModel $model) : bool
    {
        return $model->delete();
    }

    /**
     * Sets one client access inactive
     *
     * @param ServiceAccessModel $model
     * @return ModelPersistResult
     */
    public static function setClientInactive(ServiceAccessModel $model) : ModelPersistResult
    {
        $model->active = 0;
        return static::saveModel($model);
    }

    /**
     * Sets one client access active
     *
     * @param ServiceAccessModel $model
     * @return ModelPersistResult
     */
    public static function setClientActive(ServiceAccessModel $model) : ModelPersistResult
    {
        $model->active = 1;
        return static::saveModel($model);
    }

    /**
     * Updates the description
     *
     * @param ServiceAccessModel $model
     * @param string $description
     * @return ModelPersistResult
     */
    public static function updateDescription(ServiceAccessModel $model, string $description = "") : ModelPersistResult
    {
        $model->description = $description;
        return static::saveModel($model);
    }

    /**
     *
     * @param ServiceAccessModel $model
     * @return ModelPersistResult
     */
    private static function saveModel(ServiceAccessModel $model) : ModelPersistResult
    {
        if ($model->validateAndSave()) {
            return new ModelPersistResult(true, $model);
        } else {
            return new ModelPersistResult(false, $model, $model->getErrors());
        }
    }
}
