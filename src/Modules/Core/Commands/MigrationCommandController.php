<?php

namespace PHPSimpleLib\Modules\Core\Commands;

use PHPSimpleLib\Core\Controlling\CliController;
use PHPSimpleLib\Core\Data\SQLExecuter;
use PHPSimpleLib\Modules\Core\Lib\MigrationManager;
use PHPSimpleLib\Core\Data\DBConnectionManager;
use PHPSimpleLib\Modules\Core\Model\MigrationStatusModel;

class MigrationCommandController extends CliController
{

    public function initAction(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        if (MigrationManager::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            $this->logError("There is already a migration status table.");
            return;
        }

        if (MigrationManager::init('Core', $connectionName)) {
            $this->logInfo('Init completed, migration status table created.');
        } else {
            $this->logError('Init failed.');
        }
    }

    public function statusAction(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        if (!MigrationManager::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            $this->logError('There is no migration status table. Please run "Core Migration init" first.');
            return;
        }

        $model = MigrationManager::getLastMigration($connectionName);
        if ($model) {
            $this->logInfo('Last migration on ' . $model->getMigrationDate() . ' for module "' . $model->getModuleName() . '" with file "' . $model->getMigrationFile() . '"');
        } else {
            $this->logInfo('No migration information stored');
        }
    }

    public function migrateSingleAction(string $moduleName, string $migrationFileName, string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        if (!MigrationManager::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            $this->logError('There is no migration status table. Please run "Core Migration init" first.');
            return;
        }

        if (!MigrationManager::migrationFileExists($moduleName, $migrationFileName)) {
            $this->logError('Migraton file not found.');
            return;
        }

        if (MigrationManager::migrate($moduleName, $migrationFileName, $connectionName)) {
            $this->logInfo('Migration complete, status updated.');
        } else {
            $this->logError('Migration failed.');
        }
    }

    public function migrateAllAction(string $connectionName = DBConnectionManager::DEFAULT_CONNECTION_NAME): void
    {
        $this->logInfo('Migrating all non migrated files.');
        if (!MigrationManager::checkForTableExsistence(MigrationStatusModel::TABLE_NAME, $connectionName)) {
            $this->logError('There is no migration status table. Please run "Core Migration init" first.');
            return;
        }

        $all = MigrationManager::getNonMigratedFilesInModules($connectionName);
        $allMigrationFiles = [];
        foreach ($all as $moduleName => $migrationFiles) {
            foreach ($migrationFiles as $migrationFile) {
                $allMigrationFiles[$migrationFile] = $moduleName;
            }
        }
        ksort($allMigrationFiles);
        foreach ($allMigrationFiles as $migrationFile => $moduleName) {
            $this->logInfo('Migrating "' . $moduleName . '":"' . $migrationFile . '"...');
            if (MigrationManager::migrate($moduleName, $migrationFile, $connectionName)) {
                $this->logInfo('Migration for "' . $moduleName . '":"' . $migrationFile . '" complete, status updated.');
            } else {
                $this->logError('Migration failed. Aborting...');
            }
        }

        $this->logInfo('Finished.');
    }

    public function listAllAction(): void
    {
        $this->logInfo('Listing all migration files in all modules...');
        $migrationFiles = MigrationManager::getMigrationFiles();

        foreach ($migrationFiles as $moduleName => $files) {
            $this->outLine("================================");
            $this->outLine("Module: " . $moduleName);
            $this->outLine("\tFiles:");
            if (count($files) == 0) {
                $this->outLine('No migration files found.');
            }
            foreach ($files as $file) {
                $this->outLine("\t\t" . basename($file));
            }
        }
    }
}
