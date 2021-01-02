<?php

namespace PHPSimpleLib\Modules\Core\Lib;

use PHPSimpleLib\Core\Controlling\ModuleManager;
use PHPSimpleLib\Core\Data\DBConnectionManager;
use PHPSimpleLib\Core\Data\SQLExecuter;
use PHPSimpleLib\Modules\Core\Model\MigrationStatusModel;

final class MigrationManager
{
    public const DEFAULT_MIGRATION_FOLDER_NAME = "Migrations";
    private const MIGRATION_FILE_EXTENSION = ".sql";
    public const INIT_MIGRATION_FILE = "000000000001.sql";

    /**
     * Returns all found migration files for all registered modules
     *
     * @return array
     */
    public static function getMigrationFiles(): array
    {
        $result = array();

        $moduleManager = ModuleManager::getInstance();
        $moduleNames = $moduleManager->getModuleNames();

        asort($moduleNames);

        foreach ($moduleNames as $moduleName) {
            $result[$moduleName] = self::getMigrationFilesForModule($moduleName);
        }

        return $result;
    }

    /**
     * Returns all found migration files for the given module
     *
     * @param string $moduleName
     * @return array
     */
    public static function getMigrationFilesForModule(string $moduleName): array
    {
        $result = array();

        $moduleManager = ModuleManager::getInstance();
        $modulePath = $moduleManager->getModulePath($moduleName);
        $migrationFilePath = $modulePath . DIRECTORY_SEPARATOR . self::DEFAULT_MIGRATION_FOLDER_NAME;
        $migrationFiles = glob($migrationFilePath . DIRECTORY_SEPARATOR . '*' . self::MIGRATION_FILE_EXTENSION);

        foreach ($migrationFiles as $migrationFile) {
            $result[] = $migrationFile;
        }

        asort($result);

        return $result;
    }

    /**
     * Returns all non migrated files ordered in modules
     *
     * @param string $connectionName
     * @return array
     */
    public static function getNonMigratedFilesInModules(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): array
    {
        $all = self::getMigrationFiles();
        $result = array();

        foreach ($all as $moduleName => $migrationFiles) {
            $result[$moduleName] = array();
            foreach ($migrationFiles as $migrationFile) {
                if (!self::hasFileBeenMigrated($moduleName, basename($migrationFile))) {
                    $result[$moduleName][] = basename($migrationFile);
                }
            }
        }

        foreach ($result as $moduleName => $migrationFiles) {
            if (count($migrationFiles) === 0) {
                unset($result[$moduleName]);
            } else {
                asort($result[$moduleName]);
            }
        }

        return $result;
    }

    /**
     * Checks if a table exists.
     *
     * @param string $tableName
     * @param string $connectionName
     * @return boolean
     */
    public static function checkForTableExsistence(string $tableName, string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        $connectionManager = DBConnectionManager::getInstance();
        $connection = $connectionManager->getConnection($connectionName)->getConnection();
        try {
            $result = $connection->query("SELECT 1 FROM " . $tableName . " LIMIT 1")->execute();
        } catch (\Exception $ex) {
            return false;
        }
        return $result !== false;
    }

    /**
     *
     * @param string $connectionName
     * @return boolean
     */
    public static function init(string $moduleName = 'Core', string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        $moduleManager = ModuleManager::getInstance();
        $modulePath = $moduleManager->getModulePath($moduleName);
        $migrationFilePath = $modulePath . DIRECTORY_SEPARATOR . self::DEFAULT_MIGRATION_FOLDER_NAME;

        $migrationFile = $migrationFilePath . DIRECTORY_SEPARATOR . self::INIT_MIGRATION_FILE;

        if (!file_exists($migrationFile)) {
            throw new \Exception("The given migration file could not be found within the modules migration folder.");
        }

        try {
            SQLExecuter::loadAndExecute($migrationFile, $connectionName);
            $model = new MigrationStatusModel(array(
                'moduleName' => $moduleName,
                'migrationFile' => basename($migrationFile),
                'migrationDate' => date('Y-m-d H:i:s'),
                'migrationData' => file_get_contents($migrationFile)
            ));
            if ($model->validateAndSave($connectionName)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            throw $ex;
            return false;
        }
    }

    /**
     * Migrates one file
     *
     * @param string $moduleName
     * @param string $migrationFile
     * @param string $connectionName
     * @return boolean
     *
     * @throws \Exception on missing tables, file or execution failures
     */
    public static function migrate(string $moduleName, string $migrationFile, string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        if (!self::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            throw new \Exception("For the given db connection the migration status table is missing.");
        }

        if (self::hasFileBeenMigrated($moduleName, $migrationFile, $connectionName)) {
            throw new \Exception("File has already bin migrated.");
        }

        $moduleManager = ModuleManager::getInstance();
        $modulePath = $moduleManager->getModulePath($moduleName);
        $migrationFilePath = $modulePath . DIRECTORY_SEPARATOR . self::DEFAULT_MIGRATION_FOLDER_NAME;

        $migrationFile = $migrationFilePath . DIRECTORY_SEPARATOR . $migrationFile;

        if (!file_exists($migrationFile)) {
            throw new \Exception("The given migration file could not be found within the modules migration folder.");
        }

        try {
            SQLExecuter::loadAndExecute($migrationFile, $connectionName);
            $model = new MigrationStatusModel(array(
                'moduleName' => $moduleName,
                'migrationFile' => basename($migrationFile),
                'migrationDate' => date('Y-m-d H:i:s'),
                'migrationData' => file_get_contents($migrationFile)
            ));
            if ($model->validateAndSave($connectionName)) {
                return true;
            } else {
                return false;
            }
        } catch (\Exception $ex) {
            throw $ex;
            return false;
        }
    }

    /**
     * Returns the last registered migration information
     *
     * @param string $connectionName
     * @return MigrationStatusModel|null
     *
     * @throws \Exception if the migration table is missing
     */
    public static function getLastMigration(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): ?MigrationStatusModel
    {
        if (!self::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            throw new \Exception("For the given db connection the migration status table is missing.");
        }
        return MigrationStatusModel::repository()->setConnection($connectionName)->orderByDesc('migrationDate')->limit(1)->readOne();
    }

    /**
     * Checks if a migration file exists
     *
     * @param string $moduleName
     * @param string $migrationFile
     * @return boolean
     */
    public static function migrationFileExists(string $moduleName, string $migrationFile): bool
    {
        $moduleManager = ModuleManager::getInstance();
        $modulePath = $moduleManager->getModulePath($moduleName);
        $migrationFilePath = $modulePath . DIRECTORY_SEPARATOR . self::DEFAULT_MIGRATION_FOLDER_NAME;

        $migrationFile = $migrationFilePath . DIRECTORY_SEPARATOR . $migrationFile;

        return file_exists($migrationFile);
    }

    /**
     * Returns if a migration has happened
     *
     * @param string $moduleName
     * @param string $migrationFile
     * @param string $connectionName
     * @return boolean
     */
    public static function hasFileBeenMigrated(string $moduleName, string $migrationFile, string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): bool
    {
        if (!self::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            throw new \Exception("For the given db connection the migration status table is missing.");
        }

        return (bool)(MigrationStatusModel::repository()
        ->setConnection($connectionName)
        ->where('moduleName', $moduleName)
        ->where('migrationFile', $migrationFile)
        ->limit(1)
        ->count() === 1);
    }
}
