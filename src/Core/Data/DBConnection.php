<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

class DBConnection
{
    use \PHPSimpleLib\Core\ObjectFactory\Instanciator;
                                                                                                                                        use ConfigReaderTrait;


    const DEFAULT_PORT = 3306;
    const DEFAULT_HOST = 'localhost';
    const DB_TYPE_MYSQL = 'mysql';
    const DB_TYPE_SQLITE = 'sqlite';
    const DB_TYPE_SQLITE2 = 'sqlite2';
    const DB_TYPE_PGSQL = 'pgsql';
    const DB_TYPE_SQLSRV = 'sqlsrv';
    const DB_TYPE_DBLIB = 'dblib';
    const DB_TYPE_MSSQL = 'mssql';
    const DB_TYPE_SYBASE = 'sybase';
    const DB_TYPE_FIREBIRD = 'firebird';
    const LIMIT_STYLE_TOP_N = "top";
    const LIMIT_STYLE_LIMIT = "limit";
/**
     *
     * @var string
     */
    private $databaseName = null;
/**
     *
     * @var string
     */
    private $username = null;
/**
     *
     * @var string
     */
    private $password = null;
/**
     *
     * @var string
     */
    private $host = null;
/**
     *
     * @var int
     */
    private $port = null;
/**
     *
     * @var array
     */
    private $options = array();
/**
     *
     * @var string
     */
    private $currentDbType = null;
/**
     *
     * @var \PDOStatement
     */
    private $lastStatement = null;
/**
     *
     * @var \PDO
     */
    private $connection = null;
    public function __construct($config = array())
    {
        $this->config = $config;
        $this->currentDbType = $this->getConfig('type', self::DB_TYPE_MYSQL);
        $this->databaseName = $this->getConfig('db');
        $this->host = $this->getConfig('host', self::DEFAULT_PORT);
        $this->port = $this->getConfig('port', self::DEFAULT_PORT);
        $this->username = $this->getConfig('user');
        $this->password = $this->getConfig('password');
        $this->options = $this->getConfig('options', array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_STRINGIFY_FETCHES => false,
            \PDO::ATTR_EMULATE_PREPARES => false,
        ));
        if ($this->getConfig('autoconnect', false)) {
            $this->connect();
        }
    }

    public function connect()
    {
        $connectionString = $this->currentDbType . ':host=' . $this->host . ';port=' . $this->port . ';dbname=' . $this->databaseName . ';charset=utf8mb4';
        $this->connection = new \PDO($connectionString, $this->username, $this->password, $this->options);
    }

    /**
     *
     * @return \PDO
     */
    public function getConnection(): \PDO
    {
        return $this->connection;
    }

    /**
     *
     * @return string
     */
    public function getQuoteIdentifier(): string
    {
        switch ($this->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case self::DB_TYPE_PGSQL:
            case self::DB_TYPE_SQLSRV:
            case self::DB_TYPE_DBLIB:
            case self::DB_TYPE_MSSQL:
            case self::DB_TYPE_SYBASE:
            case self::DB_TYPE_FIREBIRD:
                return '"';
            case self::DB_TYPE_MYSQL:
            case self::DB_TYPE_SQLITE:
            case self::DB_TYPE_SQLITE2:
            default:
                return '`';
        }
    }

    public function getLimitStyle(): string
    {
        switch ($this->getConnection()->getAttribute(\PDO::ATTR_DRIVER_NAME)) {
            case self::DB_TYPE_SQLSRV:
            case self::DB_TYPE_DBLIB:
            case self::DB_TYPE_MSSQL:
                return self::LIMIT_STYLE_TOP_N;
            default:
                return self::LIMIT_STYLE_LIMIT;
        }
    }

    /**
     *
     * @return \PDOStatement
     */
    public function getLastStatement(): \PDOStatement
    {
        return $this->lastStatement;
    }

    /**
     *
     * @param string $query
     * @param array $parameters
     * @return bool
     */
    public function execute(string $query, $parameters = array()): bool
    {
        $statement = $this->getConnection()->prepare($query);
        $this->lastStatement = $statement;
        foreach ($parameters as $key => &$param) {
            if (is_null($param)) {
                $type = \PDO::PARAM_NULL;
            } elseif (is_bool($param)) {
                $type = \PDO::PARAM_BOOL;
            } elseif (is_int($param)) {
                $type = \PDO::PARAM_INT;
            } else {
                $type = \PDO::PARAM_STR;
            }

            $statement->bindParam(is_int($key) ? ++$key : $key, $param, $type);
        }

        $q = $statement->execute();
        return $q;
    }

    /**
     *
     * @param string $query
     * @param array $parameters
     * @return array
     */
    public function getRows(string $query, $parameters = array()): array
    {
        $this->execute($query, $parameters);
        $statement = $this->getLastStatement();
        $rows = array();
        while ($row = $statement->fetch(\PDO::FETCH_ASSOC)) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     *
     * @param string $query
     * @param array $parameters
     * @return \PDOStatement
     */
    public function getExecutedStatement(string $query, $parameters = array()): \PDOStatement
    {
        $this->execute($query, $parameters);
        return $this->getLastStatement();
    }
}
