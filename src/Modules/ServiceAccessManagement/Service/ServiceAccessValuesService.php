<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Service;

use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessValuesModel;
use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessModel;
use PHPSimpleLib\Core\Data\ModelPersistResult;

final class ServiceAccessValuesService
{

    /**
     * Returns one ServiceAccessValuesModel if found
     *
     * @param integer $id
     * @return ServiceAccessValuesModel|null
     */
    public static function findById(int $id) : ?ServiceAccessValuesModel
    {
        return ServiceAccessValuesModel::findOneById($id);
    }

    /**
     * Adds one new value entry
     *
     * @param ServiceAccessModel $serviceAccess
     * @param string $name
     * @param string $content
     * @return ModelPersistResult
     */
    public static function addValueToServiceAccess(ServiceAccessModel $serviceAccess, string $name, string $content = "") : ModelPersistResult
    {
        $model = new ServiceAccessValuesModel(array(
            'serviceaccessid' => $serviceAccess->getId(),
            'name' => $name,
            'content' => $content
        ));

        return static::saveModel($model);
    }

    /**
     * Updates one value entry
     *
     * @param ServiceAccessValuesModel $accessValuesModel
     * @param string $value
     * @return ModelPersistResult
     */
    public static function updateValue(ServiceAccessValuesModel $accessValuesModel, string $content) : ModelPersistResult
    {
        $accessValuesModel->content = $content;
        return static::saveModel($accessValuesModel);
    }

    /**
     * Removes one value entry
     *
     * @param ServiceAccessValuesModel $accessValuesModel
     * @return boolean
     */
    public static function removeValue(ServiceAccessValuesModel $accessValuesModel) : bool
    {
        return $accessValuesModel->delete();
    }

    /**
     *
     * @param ServiceAccessValuesModel $model
     * @return ModelPersistResult
     */
    private static function saveModel(ServiceAccessValuesModel $model) : ModelPersistResult
    {
        if ($model->validateAndSave()) {
            return new ModelPersistResult(true, $model);
        } else {
            return new ModelPersistResult(false, $model, $model->getErrors());
        }
    }
}
