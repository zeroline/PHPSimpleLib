<?php
/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\DBConnection;

class DBConnectionManager
{
    use \PHPSimpleLib\Core\ObjectFactory\Singleton;
    use ConfigReaderTrait;

    private const CONFIG_CONNECTIONS_KEY = "connections";
    public const DEFAULT_CONNECTION_NAME = "default";

    private $connections = array();

    public function __construct(array $config = array())
    {
        $this->config = $config;
        $connections = $this->getConfig(self::CONFIG_CONNECTIONS_KEY, array());
        foreach ($connections as $name => $connection) {
            // DBConnection::getInstance is not for singleton !
            // It uses the Instanciator trait to trigger the annotation parser for injections.
            $this->connections[$name] = DBConnection::getInstance($connection);
        }

        if (count($connections)) {
            if (!array_key_exists(self::DEFAULT_CONNECTION_NAME, $this->connections)) {
                throw new \Exception('No default database connection in configuration found.');
            }
        }
    }

    /**
     * Returns a db connection object
     *
     * @param string $key
     * @return DBConnection|null
     */
    public function getConnection(string $key = self::DEFAULT_CONNECTION_NAME) : ?DBConnection
    {
        if (array_key_exists($key, $this->connections)) {
            return $this->connections[$key];
        }
        return null;
    }
}
