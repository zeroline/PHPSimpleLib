<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Controlling;

use PHPSimpleLib\Core\Data\Model;
use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\ModelPersistResult;
use PHPSimpleLib\Core\Data\DBConnectionManager;

class HttpCrudController extends HttpController
{
    const JSON_DATA_META_RESPONSE_KEYWORD = '_dataMeta';
    protected $crudClass = null;
    protected $crudIdFieldName = 'id';
    protected $crudRequestFieldLimit = 'limit';
    protected $crudRequestFieldOffset = 'offset';
    protected $crudRequestFieldSearch = 'search';
    protected $crudRequestFieldSearchExpr = 'searchExpr';
    protected $crudRequestFieldSearchValue = 'searchValue';
    protected $crudRequestFieldOrder = 'order';
    protected $routePrefix = '/api';
    protected $routeVersion = 1;
    protected $routeResourceName = null;
    protected $routeIdFieldExpression = '([\d])';
    protected $withMetaData = true;
    protected $dbConnectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME;
    protected function prepareIndex()
    {
        $search = $this->get($this->crudRequestFieldSearch, null);
        $searchExpr = $this->get($this->crudRequestFieldSearchExpr, null);
        $searchValue = $this->get($this->crudRequestFieldSearchValue, null);
        $order = $this->get($this->crudRequestFieldOrder, null);
        $limit = $this->get($this->crudRequestFieldLimit, null);
        $offset = $this->get($this->crudRequestFieldOffset, null);
        $modelClass = $this->crudClass;
        if ($search) {
            $searchConditions = explode(',', $search);
            foreach ($searchConditions as $searchCondition) {
                $tmp = explode('=', $searchCondition);
                if (count($tmp) === 2) {
                    $modelClass::repository()->where($tmp[0], $tmp[1]);
                }
            }
        }

        if ($searchExpr) {
            $modelClass::repository()->whereLike($searchExpr, $searchValue);
        }

        if ($order) {
            $orders = explode(',', $order);
            foreach ($orders as $orderCondtition) {
                $tmp = explode(' ', $orderCondtition);
                if (count($tmp) === 2) {
                    $direction = strtoupper($tmp[1]);
                    if (in_array($direction, array('ASC', 'DESC'))) {
                        $modelClass::repository()->orderBy($tmp[0], $direction);
                    }
                }
            }
        }

        if (!is_null($limit) && !empty($limit)) {
            $modelClass::repository()->limit($limit);
        }

        if (!is_null($offset) && !empty($offset)) {
            $modelClass::repository()->offset($offset);
        }

        $results = $modelClass::repository()->read();
        $allCount = 0;
        if ($search) {
            $searchConditions = explode(',', $search);
            foreach ($searchConditions as $searchCondition) {
                $tmp = explode('=', $searchCondition);
                if (count($tmp) === 2) {
                    $modelClass::repository()->where($tmp[0], $tmp[1]);
                }
            }
        }
        $allCount = $modelClass::repository()->count();
        return (object)[
            'count' => count($results),
            'countAll' => $allCount,
            'offset' => $offset,
            'limit' => $limit,
            'search' => $search,
            'order' => $order,
            'results' => $results
        ];
    }

    public function indexAction()
    {
        $data = $this->prepareIndex();
        if ($this->withMetaData) {
            return $this->responseSuccess(array_merge(array(
                self::JSON_DATA_META_RESPONSE_KEYWORD => array(
                    'count' => $data->count,
                    'countAll' => $data->countAll,
                    'offset' => $data->offset,
                    'limit' => $data->limit,
                    'search' => $data->search,
                    'order' => $data->order
                ),
                $data->results
                )));
        } else {
            return $this->responseSuccess($data->results);
        }
    }

    public function detailAction($id)
    {
        $result = $this->autoGetModel($id);
        if ($result instanceof $this->crudClass) {
            return $this->responseSuccess($result);
        } else {
            return $result;
        }
    }

    public function updateAction($id)
    {
        $result = $this->autoGetModel($id);
        if ($result instanceof $this->crudClass) {
            $data = $this->getBodyDataContainer();
            $fields = $result->getExistingFieldNames();
            foreach ($fields as $fieldName) {
                if (isset($data->{$fieldName})) {
                    $result->{$fieldName} = $data->{$fieldName};
                }
            }
            list($valid, $model, $errors) = $this->saveModel($result)->asArray();
            if ($valid) {
                return $this->responseSuccess($model);
            } else {
                return $this->responseInvalidData($errors);
            }
        } else {
            return $result;
        }
    }

    public function deleteAction($id)
    {
        $result = $this->autoGetModel($id);
        if ($result instanceof $this->crudClass) {
            $deletedId = $result->getId();
            if ($result->delete()) {
                return $this->responseSuccess(array(
                    'id' => $deletedId,
                ), 'deleted');
            } else {
                return $this->responseError();
            }
        } else {
            return $result;
        }
    }

    public function createAction()
    {
        $data = $this->getBodyDataContainer();
        $newModel = new $this->crudClass();
        foreach ($data->getData() as $fieldName => $fieldValue) {
            $newModel->{$fieldName} = $fieldValue;
        }
        list($valid, $model, $errors) = $this->saveModel($newModel)->asArray();
        if ($valid) {
            return $this->responseSuccess($model, '', 201);
        } else {
            return $this->responseInvalidData($errors);
        }
    }

    /**
     *
     * @return string
     */
    protected function getIdFromRequest(): string
    {
        return $this->get($this->crudIdFieldName);
    }

    /**
     *
     * @param mixed $id
     * @return Model
     */
    protected function getModelById($id): Model
    {
        $c = $this->crudClass;
        return $c::findOneById($id);
    }

    /**
     *
     * @return Model
     */
    protected function autoGetModel($id = null): Model
    {
        if (is_null($id)) {
            $id = $this->getIdFromRequest();
        }
        if ($id) {
            return $this->getModelById($id);
        }
        return $this->responseNotFound();
    }

    /**
     *
     * @param Model $model
     * @return ModelPersistResult
     */
    protected function saveModel(Model $model): ModelPersistResult
    {
        if ($model instanceof DatabaseAbstractionModel) {
            if ($model->validateAndSave($this->getConnectionName())) {
                return new ModelPersistResult(true, $model);
            } else {
                return new ModelPersistResult(false, $model, $model->getErrors());
            }
        } else {
            if ($model->save($this->getConnectionName())) {
                return new ModelPersistResult(true, $model);
            } else {
                return new ModelPersistResult(false, $model);
            }
        }
    }

    /**
     * Sets the connection name
     *
     * @param string $connectionName
     * @return void
     */
    protected function setConnectionName(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        $this->dbConnectionName = $connectionName;
    }

    /**
     * Gets the connection name
     *
     * @return string
     */
    protected function getConnectionName(): string
    {
        return $this->dbConnectionName;
    }

    /**
     * Setups the whole controller for managing one REST source.
     * It builds 5 routes:
     * - GET [$prefix]/v[$version]/[$resourceName] for searching
     * - GET [$prefix]/v[$version]/[$resourceName]/[$idExpression] for single resource details
     * - PUT [$prefix]/v[$version]/[$resourceName]/[$idExpression] for updating one resource
     * - POST [$prefix]/v[$version]/[$resourceName]/[$idExpression] for creating one new resource
     * - DELETE [$prefix]/v[$version]/[$resourceName]/[$idExpression] for deleting one resource
     *
     * @param string $prefix
     * @param string $resourceName
     * @param integer $version
     * @param string $idExpression
     * @return void
     */
    protected function setupAutoRouting(string $prefix, string $resourceName, $version = 1, $idExpression = '([\d])')
    {
        $this->routePrefix = $prefix;
        $this->routeResourceName = $resourceName;
        $this->routeVersion = $version;
        $this->routeIdFieldExpression = $idExpression;
        $basePath = $this->routePrefix . (isset($version) ? '/v' . $this->routeVersion : '') . '/' . $this->routeResourceName;
        $basePathSingleResource = $basePath . '/' . $this->routeIdFieldExpression;
        $this->addRouteMapping('GET:' . $basePath, 'index');
        $this->addRouteMapping('GET:' . $basePathSingleResource, 'detail');
        $this->addRouteMapping('PUT:' . $basePathSingleResource, 'update');
        $this->addRouteMapping('POST:' . $basePathSingleResource, 'create');
        $this->addRouteMapping('DELETE:' . $basePathSingleResource, 'delete');
    }
}
