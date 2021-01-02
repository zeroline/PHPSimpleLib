<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\ValidatedModel;
use PHPSimpleLib\Core\Data\DBConnectionManager;
use PHPSimpleLib\Core\Event\Mediator;
use PHPSimpleLib\Core\Event\EventArgs;

class DatabaseAbstractionModel extends ValidatedModel
{
    public const CONVENTION_NAMING_SUFFIX = 'Model';

    public const CONNECT_TYPE_SIMPLE = "CSIMPLE";
    public const CONNECT_TYPE_COMPLEX = "CCOMPLEX";

    public const EVENT_CREATED = 'created';
    public const EVENT_CHANGED = 'changed';
    public const EVENT_DELETED = 'deleted';

    /**
     *
     * @var string
     */
    protected $tableName = '';

    /**
     *
     * @var string
     */
    protected $idColumn = 'id';

    /**
     * @var \PHPSimpleLib\Core\Data\GenericRepository
     */
    protected static $searchRepository = null;

    /**
     *
     * @var \PHPSimpleLib\Core\Event\Mediator
     */
    protected $mediator = null;

    /**
     * Store field info for joining
     *
     * @var array
     */
    protected $fieldConnectStatements = array();

    /**
     * Handle joins
     *
     * @var array
     */
    protected $connectedData = array();

    /**
     *
     * @var array
     */
    protected $useStdFields = array();

    /**
     * Stores single fetched models, that came over findOneById
     *
     * @var array
     */
    protected static $singleModelCache = array();

    /**
     * stores one model in the static cache
     *
     * @param string $class
     * @param integer $id
     * @param Model $model
     * @return void
     */
    private static function addToModelCache(string $class, int $id, Model $model): void
    {
        $key = $class . ':' . $id;
        static::$singleModelCache[$key] = $model;
    }

    /**
     * Retrieves one cached model if found
     *
     * @param string $class
     * @param integer $id
     * @return Model|null
     */
    private static function getModelFromCache(string $class, int $id): ?Model
    {
        if (static::isModelInCache($class, $id)) {
            $key = $class . ':' . $id;
            return static::$singleModelCache[$key];
        }
        return null;
    }

    /**
     * indicates if one model has been cached
     *
     * @param string $class
     * @param integer $id
     * @return boolean
     */
    private static function isModelInCache(string $class, int $id): bool
    {
        $key = $class . ':' . $id;
        return array_key_exists($key, static::$singleModelCache);
    }

    /**
     *
     * @param string $event
     * @return string
     */
    public static function getEventName(string $event): string
    {
        return get_called_class() . '.' . $event;
    }

    /**
     *
     * @param array $data
     */
    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->mediator = Mediator::getInstance();

        if (empty($this->tableName)) {
            $classNamePartials = explode('\\', get_called_class());
            $simplifiedClassName = $classNamePartials[count($classNamePartials) - 1];
            $this->tableName = strtolower(str_replace(self::CONVENTION_NAMING_SUFFIX, '', $simplifiedClassName));
        }

        if($this->getFilterMode() === EnumFilterModes::FILTER_MODE_AFTER_AFTER_FETCH || $this->getFilterMode() === EnumFilterModes::FILTER_MODE_BOTH) {
            $this->filter();
        }
    }

    /**
     *
     * @return bool
     */
    public function isNew(): bool
    {
        return (bool) (!isset($this->{$this->idColumn}) || empty($this->{$this->idColumn}));
    }

    /**
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }

    /**
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->{$this->idColumn};
    }

    /**
     *
     * @return string
     */
    public function getIdColumn(): string
    {
        return $this->idColumn;
    }

    /**
     *
     * @param string $name
     * @param callable $function
     */
    protected function addAutomaticField(string $name, callable $function)
    {
        $this->useStdFields[$name] = $function;
    }

    /**
     *
     */
    protected function handleAutomaticFieldsOnSave()
    {
        if ((count($this->useStdFields) > 0)) {
            foreach ($this->useStdFields as $fieldName => $callable) {
                $callable($this);
            }
        }
    }

    /**
     * Adds one simple connection to another model class
     *
     * Example:
     * Model A has one or multiple model B through B.A_ID = A.ID
     * addSimpleConnectedField('MyBs', B::class, 'A_ID', 'ID');
     *
     * @param string $fieldName
     * @param string $modelClass
     * @param string $foreignFieldName
     * @param string $ownFieldName
     * @return void
     */
    protected function addSimpleConnectedField(string $fieldName, string $modelClass, string $foreignFieldName, string $ownFieldName): void
    {
        $this->fieldConnectStatements[$fieldName] = [
            'modelClass' => $modelClass,
            'connectType' => self::CONNECT_TYPE_SIMPLE,
            'foreignFieldName' => $foreignFieldName,
            'ownFieldName' => $ownFieldName
        ];
        $this->connectedData[$fieldName] = array();
    }

    /**
     * Adds one complex connection to another model class
     *
     * Example:
     * Model A has one or multiple model B using the model A_B
     * A.AID is the primary key
     * A_B has A_ID and B_ID as keys
     * B.BID is the second primary key
     *
     * addComplexConnectedField('MyBs', B::class, A_B::class, 'BID', 'A_ID', 'B_ID', 'AID');
     *
     * This generates
     *
     * SELECT [B].* FROM [B] JOIN [A_B] ON [A_B].[B_ID] = [B].[BID] WHERE [A_B].[A_ID] = [A].[AID]
     *
     * @param string $fieldName
     * @param string $modelClass
     * @param string $connectionClass
     * @param string $foreignFieldName
     * @param string $leftIdColumn
     * @param string $rightIdColumn
     * @return void
     */
    protected function addComplexConnectedField(string $fieldName, string $modelClass, string $connectionClass, string $foreignFieldName, string $leftIdColumn, string $rightIdColumn, string $ownFieldName): void
    {
        $this->fieldConnectStatements[$fieldName] = [
            'modelClass' => $modelClass,
            'connectType' => self::CONNECT_TYPE_COMPLEX,
            'foreignFieldName' => $foreignFieldName,
            'leftIdColumn' => $leftIdColumn,
            'rightIdColumn' => $rightIdColumn,
            'ownFieldName' => $ownFieldName,
            'connectionClass' => $connectionClass
        ];
        $this->connectedData[$fieldName] = array();
    }

    /**
     * Perform the selections a defined in for the
     * connected fields
     *
     * @return void
     */
    protected function handleAllConnectedStatements(): void
    {
        foreach ($this->fieldConnectStatements as $fieldName => $connectData) {
            $this->handleSingleFieldConnectedStatement($fieldName);
        }
    }

    /**
     * Perform the selections a defined in for one
     * connected field
     *
     * @param string $fieldName
     * @return array
     */
    protected function handleSingleFieldConnectedStatement(string $fieldName): array
    {
        if (!$this->hasConnectedData($fieldName)) {
            $connectData = $this->fieldConnectStatements[$fieldName];
            $modelClass = $connectData['modelClass'];
            $foreignFieldName = $connectData['foreignFieldName'];
            $connectType = $connectData['connectType'];
            $ownFieldName = $connectData['ownFieldName'];

            if ($connectType === self::CONNECT_TYPE_SIMPLE) {
                $modelClass::repository()->where($foreignFieldName, $this->{$ownFieldName});
                $this->connectedData[$fieldName] = $modelClass::repository()->read();
            } else if (self::CONNECT_TYPE_COMPLEX) {
                $targetTableName = $modelClass::repository()->getTableName();
                $connectionClass = $connectData['connectionClass'];
                $connectionTableName = $connectionClass::repository()->getTableName();
                $rightIdColumn = $connectData['rightIdColumn'];
                $leftIdColumn = $connectData['leftIdColumn'];
                $this->connectedData[$fieldName] = $modelClass::repository()->readRaw('SELECT ' . $targetTableName . '.* FROM ' . $targetTableName . ' JOIN ' . $connectionTableName . ' ON ' . $connectionTableName . '.' . $rightIdColumn . '=' . $targetTableName . '.' . $foreignFieldName . ' WHERE ' . $connectionTableName . '.' . $leftIdColumn . '=' . $this->{$ownFieldName});
                // SELECT role.* FROM role JOIN user_role ON user_role.roleid = role.id WHERE user_role.userid = 2
                // SELECT [targetTableName].* FROM [targetTableName] JOIN [connectionTableName] ON [connectionTableName].[rightIdColumn] = [targetTableName].[foreignFieldName] WHERE [connectionTableName].[leftIdColumn] = [this].[ownFieldName]
            }
        }

        return $this->connectedData[$fieldName];
    }

    /**
     * Checks if data from one connected field is available
     *
     * @param string $fieldName
     * @return boolean
     */
    public function hasConnectedData(string $fieldName): bool
    {
        return (count($this->connectedData[$fieldName]) > 0);
    }

    /**
     * Return data from a connected field
     *
     * @param string $fieldName
     * @return array
     */
    public function getConnectedData(string $fieldName): array
    {
        return $this->handleSingleFieldConnectedStatement($fieldName);
    }

    /**
     * @param string $connectionName
     *
     * @return bool
     */
    public function save(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        $repository = static::repository();
        $repository->clearConditions();
        $repository->setTable($this->getTableName());
        $repository->setConnection($connectionName);

        $this->handleAutomaticFieldsOnSave();

        if($this->getFilterMode() === EnumFilterModes::FILTER_MODE_BEFORE_SAVE || $this->getFilterMode() === EnumFilterModes::FILTER_MODE_BOTH) {
            $this->filter();
        }

        if ($this->isNew()) {
            $newID = $repository->create($this->data);
            if ($newID !== false) {
                $this->{$this->idColumn} = $newID;
                $this->clearDirtyFields();
                $this->mediator->trigger(static::getEventName(self::EVENT_CREATED), new EventArgs(array('model' => $this)));
                return true;
            }
        } else {
            if (count($this->getDirtyFields()) === 0) {
                return true; // nothing to change
            }
            $repository->where($this->idColumn, $this->getId());
            $result = $repository->update($this->getDirtyFields());
            if ($result) {
                $this->mediator->trigger(static::getEventName(self::EVENT_CHANGED), new EventArgs(array('model' => $this, 'dirtyFields' => array_reverse(array_reverse($this->dirtyFields)))));
                $this->clearDirtyFields();
                return true;
            }
        }
        unset($repository);
        return false;
    }

    /**
     * Tries to save the model after performing validation.
     * Saving is only performed if the model is valid
     *
     * @param string $connectionName
     *
     * @return boolean
     */
    public function validateAndSave(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        $isValid = true;
        if ($this->isNew() && $this->hasScope(self::VALIDATION_SCOPE_CREATE)) {
            $isValid = $this->isValid(self::VALIDATION_SCOPE_CREATE);
        } elseif (!$this->isNew() && $this->hasScope(self::VALIDATION_SCOPE_UPDATE)) {
            $isValid = $this->isValid(self::VALIDATION_SCOPE_UPDATE);
        } else {
            $isValid = $this->isValid();
        }

        if ($isValid === true) {
            return $this->save($connectionName);
        }
        return false;
    }

    /**
     * @param string $connectionName
     *
     * @return bool
     */
    public function delete(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        $repository = static::repository();
        $repository->clearConditions();
        $repository->setTable($this->getTableName());
        $repository->setConnection($connectionName);

        if (!$this->isNew()) {
            $repository->where($this->idColumn, $this->getId());
            $clonedModel = clone $this;
            if ($deleteResult = $repository->delete()) {
                $this->mediator->trigger(static::getEventName(self::EVENT_DELETED), new EventArgs(array('model' => $clonedModel)));
                return $deleteResult;
            }
        }
        unset($repository);
        return false;
    }

    /**
     * Tries to select and return one item from the database, casting
     * it to the given model class.
     * If cache is true ( default ) the result will be stored temporary
     * as long as the script is running
     *
     * @param int $id
     * @param bool $cache
     * @param string $connectionName
     *
     * @return DatabaseAbstractionModel
     */
    public static function findOneById(int $id, bool $cache = true, string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): ?DatabaseAbstractionModel
    {
        $c = get_called_class();

        if ($cache) {
            if (static::isModelInCache($c, $id)) {
                return static::getModelFromCache($c, $id);
            }
        }

        $dummy = new $c();

        $repo = $c::getSearchRepository();

        $repo->clearConditions();
        $repo->setTable($dummy->getTableName());
        $repo->setConnection($connectionName);

        $repo->where($dummy->getIdColumn(), $id);
        $repo->limit(1);

        $rows = $repo->read();
        unset($dummy);
        if (count($rows) == 1) {
            $foundModel = new $c($rows[0]);
            if ($cache) {
                static::addToModelCache($c, $id, $foundModel);
            }
            return $foundModel;
        }
        return null;
    }

    /**
     *
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public static function repository(): GenericRepository
    {
        return static::getSearchRepository();
    }

    /**
     *
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    private static function getSearchRepository(): GenericRepository
    {
        $c = get_called_class();

        if (!isset($c::$searchRepository)) {
            $c::$searchRepository = GenericRepository::getInstance();
        }
        $dummy = new $c();
        $c::$searchRepository->setTable($dummy->getTableName());
        $c::$searchRepository->setModelClassName($c);

        unset($dummy);

        return $c::$searchRepository;
    }
}
