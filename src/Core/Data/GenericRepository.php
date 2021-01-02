<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\DBConnectionManager;
use PHPSimpleLib\Core\Data\DBConnection;
use PHPSimpleLib\Core\Logging\EnumLogLevel;
use PHPSimpleLib\Core\Logging\RuntimeFileSystemLogger;

class GenericRepository
{
    use \PHPSimpleLib\Core\ObjectFactory\Instanciator;


    const INTERNAL_FIELD_COUNT_RESULT = 'iCountResult';
    const INTERNAL_JOIN_PREFIX = 'jnd';
/**
     *
     * @Inject \PHPSimpleLib\Core\Data\DBConnectionManager
     * @var \PHPSimpleLib\Core\Data\DBConnectionManager
     */
    private $connectionManager = null;
/**
     *
     * @var string
     */
    private $modelClassName = '';
    protected $limit = null;
    protected $offset = null;
    protected $order = array();
    protected $where = array();
    protected $placeholder = array();
    protected $table = null;
    protected $selectFields = array();
    protected $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME;
    protected $joins = array();
/**
     * Returns a db connection instance from the db connection manager.
     * Null if not found.
     * Default is set to "default".
     *
     * @return DBConnection|null
     */
    protected function getConnection(): ?DBConnection
    {
        return $this->connectionManager->getConnection($this->connectionName);
    }

    /**
     *
     * @param string $name
     */
    public function setModelClassName(string $name)
    {
        $this->modelClassName = $name;
    }

    /**
     *
     * @return string
     */
    public function getModelClassName(): string
    {
        return $this->modelClassName;
    }

    /**
     *
     * @return bool
     */
    public function isModelClassNameSpecified(): bool
    {
        return (isset($this->modelClassName) && !empty($this->modelClassName));
    }

    /**
     * Sets a new connection name
     *
     * @param string $connectionName
     * @return GenericRepository
     */
    public function setConnection(string $connectionName): GenericRepository
    {
        $this->connectionName = $connectionName;
        return $this;
    }

    /**
     *
     * @param string $tableName
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function setTable(string $tableName): GenericRepository
    {
        $this->table = $tableName;
        return $this;
    }

    /**
     * Return the table name
     *
     * @return string
     */
    public function getTableName(): string
    {
        return $this->table;
    }

    /**
     *
     * @param string $fieldName
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function selectField(string $fieldName): GenericRepository
    {
        $this->selectFields[] = $fieldName;
        return $this;
    }

    // WHERE

    /**
     *
     * @param string $fieldName
     * @param mixed $value
     * @param string $operator
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereRaw(string $fieldName, $value, string $operator): GenericRepository
    {
        $this->where[] = (object) array(
            'name' => $fieldName,
            'value' => $value,
            'operator' => $operator
        );
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function where(string $name, $value): GenericRepository
    {
        if (is_null($value)) {
            $this->whereNull($name);
        } else {
            $this->whereRaw($name, $value, '=');
        }
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereNot(string $name, $value): GenericRepository
    {
        if (is_null($value)) {
            $this->whereNotNull($name);
        } else {
            $this->whereRaw($name, $value, '<>');
        }
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereLike(string $name, $value): GenericRepository
    {
        $this->whereRaw($name, $value, 'LIKE');
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereNull(string $name): GenericRepository
    {
        $this->whereRaw($name, null, 'IS NULL');
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereNotNull(string $name): GenericRepository
    {
        $this->whereRaw($name, null, 'IS NOT NULL');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereGreaterThan(string $name, $value): GenericRepository
    {
        $this->whereRaw($name, $value, '>');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereGreaterThanOrEqual(string $name, $value): GenericRepository
    {
        $this->whereRaw($name, $value, '>=');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereLowerThan(string $name, $value): GenericRepository
    {
        $this->whereRaw($name, $value, '<');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param mixed $value
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereLowerThanOrEqual(string $name, $value): GenericRepository
    {
        $this->whereRaw($name, $value, '<=');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param array $values
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereIn(string $name, array $values): GenericRepository
    {
        $this->whereRaw($name, $values, 'IN');
        return $this;
    }

    /**
     *
     * @param string $name
     * @param array $values
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function whereNotIn(string $name, array $values): GenericRepository
    {
        $this->whereRaw($name, $values, 'NOT IN');
        return $this;
    }

    // ORDER
    /**
     *
     * @param string $name
     * @param string $direction
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function orderBy(string $name, string $direction): GenericRepository
    {
        $this->order[] = (object) array(
            'name' => $name,
            'direction' => $direction
        );
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function orderByAsc(string $name): GenericRepository
    {
        $this->orderBy($name, 'ASC');
        return $this;
    }

    /**
     *
     * @param string $name
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function orderByDesc(string $name): GenericRepository
    {
        $this->orderBy($name, 'DESC');
        return $this;
    }

    // PAGING

    /**
     *
     * @param int $limit
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function limit(int $limit): GenericRepository
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     *
     * @param int $offset
     * @return \PHPSimpleLib\Core\Data\GenericRepository
     */
    public function offset(int $offset): GenericRepository
    {
        $this->offset = $offset;
        return $this;
    }

    /**
     * Add a join directive
     *
     * @param string $modelClass
     * @param string $joiningTableName
     * @param string $joiningTableFieldName
     * @param string $baseTableFieldName
     * @return GenericRepository
     */
    public function join(string $modelClass, string $joiningTableName, string $joiningTableFieldName, string $baseTableFieldName): GenericRepository
    {
        $this->joins[] = [
            'modelClass' => $modelClass,
            'joinTableName' => $joiningTableName,
            'joinTableFieldName' => $joiningTableFieldName,
            'baseTableFieldName' => $baseTableFieldName
        ];
        return $this;
    }

    //

    //

    //

    // BUILDER
    /**
     * Use engines quotes
     * @param string $name
     * @return string
     */
    private function encapsulate(string $name): string
    {
        return $this->getConnection()->getQuoteIdentifier() . $name . $this->getConnection()->getQuoteIdentifier();
    }

    /**
     * Generates placeholder name for values and uses standard quotes used in
     * the specified engine
     * @param string $name
     * @param mixed $value
     * @return string
     */
    private function encapsulatePlaceholder(string $name, $value): string
    {
        $newName = ':' . $name;
        if (array_key_exists($newName, $this->placeholder)) {
            return $this->encapsulate($name . mt_rand(), $value);
        } else {
            $this->placeholder[$newName] = $value;
            return $newName;
        }
    }

    /**
     * Build full insert query string
     * @param array $data
     * @return string
     */
    private function buildInsert(array $data = array()): string
    {
        $insertStr = 'INSERT INTO ' . $this->encapsulate($this->table) . ' ';
        $fieldsArr = array();
        $valueArr = array();
        foreach ($data as $k => $v) {
            $fieldsArr[] = $k;
            $valueArr[] = $this->encapsulatePlaceholder($k, $v);
        }

        $insertStr .= '(' . implode(',', $fieldsArr) . ') ';
        $insertStr .= 'VALUES (' . implode(',', $valueArr) . ') ';
        return $insertStr;
    }

    /**
     * Build full select count(*) query string
     * @return string
     */
    private function buildSelectCount(): string
    {
        $selectStr = 'SELECT COUNT(*) AS ' . self::INTERNAL_FIELD_COUNT_RESULT . ' ';
        $selectStr .= 'FROM ' . $this->encapsulate($this->table) . ' ';
        $selectStr .= ' ' . $this->buildWhere();
        $selectStr .= ' ' . $this->buildOrderBy();
        if (!is_null($this->limit)) {
            $selectStr .= ' LIMIT ' . $this->limit;
        }
        if (!is_null($this->limit) && !is_null($this->offset)) {
            $selectStr .= ' OFFSET ' . $this->limit;
        }

        return $selectStr;
    }

    private function buildJoin(): string
    {
        $joinString = '';
        if ($this->hasJoins()) {
            foreach ($this->joins as $joinIndex => $joinData) {
                $joiningTableName = $joinData['joinTableName'];
                $joiningTableFieldName = $joinData['joiningTableFieldName'];
                $baseTableFieldName = $joinData['baseTableFieldName'];
                $tablePrefix = self::INTERNAL_JOIN_PREFIX . $joinIndex;
                $joinString .= ' JOIN ' . $this->encapsulate($joiningTableName) . ' ' . $tablePrefix . ' ';
                $joinString .= 'ON (' . $this->encapsulate($tablePrefix . '.') . $joiningTableFieldName . '=' . $baseTableFieldName . ')';
            }
        }

        return $joinString;
    }

    /**
     *
     * @return array
     */
    private function buildGeneralSelectFields(): array
    {
        $joinSelectFields = array($this->getInstance() . '.*');
        if ($this->hasJoins()) {
            foreach ($this->joins as $joinIndex => $joinData) {
                $tablePrefix = self::INTERNAL_JOIN_PREFIX . $joinIndex;
                $joinSelectFields[] = $tablePrefix . '.*';
            }
        }

        return $joinSelectFields;
    }

    private function hasJoins(): bool
    {
        return (count($this->joins) > 0);
    }

    /**
     * Build full select query string
     * @return string
     */
    private function buildSelect(): string
    {
        $selectStr = 'SELECT ';
        if (count($this->selectFields)) {
            $selectStr .= implode(', ', $this->selectFields) . ' ';
        } else if ($this->hasJoins()) {
            $selectStr .= $this->buildGeneralSelectFields();
        } else {
            $selectStr .= '* ';
        }
        $selectStr .= 'FROM ' . $this->encapsulate($this->table) . ' ';
// Join
        $selectStr .= ' ' . $this->buildJoin();
// Where
        $selectStr .= ' ' . $this->buildWhere();
// Order
        $selectStr .= ' ' . $this->buildOrderBy();
        if (!is_null($this->limit)) {
            $selectStr .= ' LIMIT ' . $this->limit;
        }
        if (!is_null($this->limit) && !is_null($this->offset)) {
            $selectStr .= ' OFFSET ' . $this->offset;
        }

        RuntimeFileSystemLogger::getInstance()->log(EnumLogLevel::DEBUG, $selectStr);
        return $selectStr;
    }

    /**
     * Build full update query string
     * @param array $data
     * @return string
     */
    private function buildUpdate(array $data = array()): string
    {
        $updateStr = 'UPDATE ' . $this->encapsulate($this->table) . ' SET ';
        $updateArr = array();
        foreach ($data as $k => $v) {
            $updateArr[] = $k . ' = ' . $this->encapsulatePlaceholder($k, $v);
        }
        $updateStr .= implode(',', $updateArr) . ' ';
        $updateStr .= $this->buildWhere() . ' ';
        return $updateStr;
    }

    /**
     * Build full delete query string
     * @return string
     */
    private function buildDelete(): string
    {
        $deleteStr = 'DELETE FROM ' . $this->encapsulate($this->table) . ' ';
        $deleteStr .= $this->buildWhere() . ' ';
        return $deleteStr;
    }

    /**
     * Build where string. Use $combineWith to switch between OR and AND.
     * @param string $combineWith
     * @return string
     */
    private function buildWhere($combineWith = ' AND '): string
    {
        $whereStr = 'WHERE ';
        $whereArr = array();
        foreach ($this->where as $where) {
            switch ($where->operator) {
                case 'IN':
                case 'NOT IN':
                    if (count($where->value) > 0) {
                        $tmpStr = $this->encapsulate($where->name) . ' ' . $where->operator . ' (';
                        $tmpArr = array();
                        for ($i = 0; $i < count($where->value); $i++) {
                            $v = $where->value[$i];
                            $p = $where->name . $i;
                            $tmpArr[] = $this->encapsulatePlaceholder($p, $v);
                        }
                        $tmpStr .= implode(',', $tmpArr);
                        $tmpStr .= ')';
                        $whereArr[] = $tmpStr;
                    }

                    break;
                case 'IS NULL':
                case 'IS NOT NULL':
                    $whereArr[] = $this->encapsulate($where->name) . ' ' . $where->operator;

                    break;
                default:
                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                $whereArr[] = $this->encapsulate($where->name) . ' ' . $where->operator . ' ' . $this->encapsulatePlaceholder($where->name, $where->value);

                    break;
            }
        }

        $whereStr .= implode($combineWith, $whereArr);
        if ($whereStr == 'WHERE ') {
            return '';
        }

        return $whereStr;
    }

    /**
     * Build order by string
     * @return string
     */
    private function buildOrderBy(): string
    {
        $orderByStr = 'ORDER BY ';
        $orderByArr = array();
        foreach ($this->order as $order) {
            $orderByArr[] = $order->name . ' ' . $order->direction;
        }

        $orderByStr .= implode(',', $orderByArr);
        if ($orderByStr == 'ORDER BY ') {
            return '';
        }

        return $orderByStr;
    }

    // PERFORMER

    /**
     * Clears all conditions
     */
    public function clearConditions()
    {
        $this->limit = null;
        $this->offset = null;
        $this->order = array();
        $this->placeholder = array();
        $this->selectFields = array();
        $this->table = '';
        $this->where = array();
        $this->modelClassName = '';
        $this->joins = array();
    }

    /**
     * Creates and performs an instert statement with the given data.
     * Return the last inserted id
     *
     * @param array $data
     * @return string
     */
    public function create(array $data = array())
    {
        $query = $this->buildInsert($data);
        $result = false;
        if ($this->getConnection()->execute($query, $this->placeholder)) {
            $result = $this->getConnection()->getConnection()->lastInsertId();
        }
        $this->clearConditions();
        return $result;
    }

    /**
     *
     * @return int
     */
    public function count(): int
    {
        $query = $this->buildSelectCount();
        $result = $this->getConnection()->getRows($query, $this->placeholder);
        $this->clearConditions();
        if ($result && isset($result[0]) && isset($result[0][self::INTERNAL_FIELD_COUNT_RESULT])) {
            return intval($result[0][self::INTERNAL_FIELD_COUNT_RESULT]);
        }

        return null;
    }

    /**
     * Perfoms a select statement with the previous given parameters
     * Returns raw rows
     * @return array
     */
    public function read(): array
    {
        $result = array();
        if ($this->isModelClassNameSpecified()) {
            $result = $this->readModels($this->getModelClassName());
        } else {
            $query = $this->buildSelect();
            $result = $this->getConnection()->getRows($query, $this->placeholder);
            $this->clearConditions();
        }

        return $result;
    }

    /**
     * Same as @see read but returns model objects of the given class
     * @param string $modelClass
     * @return array
     */
    public function readModels($modelClass): array
    {
        $query = $this->buildSelect();
        $rows = $this->getConnection()->getRows($query, $this->placeholder);
        $models = array();
        foreach ($rows as $row) {
            $models[] = new $modelClass((is_array($row) ? $row : (array) $row));
        }
        $this->clearConditions();
        return $models;
    }

    /**
     * Same as @see read or @see readModels but returns only one row/instance
     * @return mixed
     */
    public function readOne()
    {
        $result = null;
        $rows = array();
        $this->limit(1);
        if ($this->isModelClassNameSpecified()) {
            $rows = $this->readModels($this->getModelClassName());
        } else {
            $rows = $this->read();
        }

        if (isset($rows[0])) {
            return $rows[0];
        }

        return $result;
    }

    /**
     * Performs a raw query and returns rows or instances
     * @param string $query
     * @return array
     */
    public function readRaw($query): array
    {
        $rows = $this->getConnection()->getRows($query, array());
        if ($this->isModelClassNameSpecified()) {
            $models = array();
            $modelClass = $this->getModelClassName();
            foreach ($rows as $row) {
                $models[] = new $modelClass((is_array($row) ? $row : (array) $row));
            }
            return $models;
        } else {
            return $rows;
        }
    }

    /**
     * Performs an update query
     * @param array $data
     * @return bool
     */
    public function update(array $data = array()): bool
    {
        $query = $this->buildUpdate($data);
        $result =  $this->getConnection()->execute($query, $this->placeholder);
        $this->clearConditions();
        return $result;
    }

    /**
     * Performs a delete query
     * @return bool
     */
    public function delete(): bool
    {
        $query = $this->buildDelete();
        $result = $this->getConnection()->execute($query, $this->placeholder);
        $this->clearConditions();
        return $result;
    }

    /**
     * Executes the given raw statement
     *
     * @param string $sqlStatement
     * @return boolean
     */
    public function executeRaw(string $sqlStatement): bool
    {
        return $this->getConnection()->execute($sqlStatement);
    }

    /************************************************************************/

    /**
     * Start a transaction
     *
     * @return boolean
     */
    public function beginTransaction(): bool
    {
        return $this->getConnection()->getConnection()->beginTransaction();
    }

    /**
     * Commit the current transaction changes
     *
     * @return boolean
     */
    public function commitTransaction(): bool
    {
        return $this->getConnection()->getConnection()->commit();
    }

    /**
     * Rollback the current transaction changes
     *
     * @return boolean
     */
    public function rollbackTransaction(): bool
    {
        return $this->getConnection()->getConnection()->rollBack();
    }
}
