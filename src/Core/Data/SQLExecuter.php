<?php

/* Copyright (C) Frederik Nieß <fred@zeroline.me> - All Rights Reserved */

namespace PHPSimpleLib\Core\Data;

use PHPSimpleLib\Core\Data\DBConnectionManager;
use PHPSimpleLib\Core\Data\GenericRepository;

final class SQLExecuter
{
    /**
     * Read and execute an sql file. All found queries are executed
     * within a transaction environment. If one query fails no query will be
     * commited
     *
     * @param string $sqlFilename
     * @param string $connection
     *
     * @throws \Exception on script exec error
     *
     * @return void
     */
    public static function loadAndExecute(string $sqlFilename, string $connection = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        if (!is_readable($sqlFilename)) {
            throw new \Exception('SQL file "' . $sqlFilename . '" is not readable.');
        }
        $repository = GenericRepository::getInstance();
        $repository->setConnection($connection);
        //$repository->beginTransaction();
        $result = null;
        $query = '';
        $fileLines = file($sqlFilename);
        foreach ($fileLines as $line) {
            $startWith = substr(trim($line), 0, 2);
            $endWith = substr(trim($line), -1, 1);
            if (empty($line) || $startWith == '--' || $startWith == '/*' || $startWith == '//') {
                continue;
            }

            $query = $query . $line;
            if ($endWith == ';') {
                $result = $repository->executeRaw($query);
                if ($result === false) {
                    //$repository->rollbackTransaction();
                    throw new \Exception('Execution of SQL script file "' . $sqlFilename . '" aborted. Failure at script line "' . $query . '"');
                    break;
                }
                $query = '';
            }
        }

        /*
        if ($result === true) {
            $repository->commitTransaction();
        } else {
            $repository->rollbackTransaction();
        }
        */

        // TODO: Table creation does not benefit from transactions
        // MySQL does not rollback or commit that kind of executions
    }
}
