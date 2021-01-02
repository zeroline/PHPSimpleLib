<?php

namespace PHPSimpleLib\Modules\ServiceAccessManagement\Commands;

use PHPSimpleLib\Core\Controlling\CliController;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Service\ServiceAccessValuesService;
use PHPSimpleLib\Modules\ServiceAccessManagement\Model\ServiceAccessValuesModel;

class ManagerCommandController extends CliController
{
    public function statusAction(): void
    {
        $this->logInfo('Fetching all client access information...');
        $results = ServiceAccessService::getAllClients();

        if (count($results) > 0) {
            foreach ($results as $serviceAccess) {
                $this->outLine($serviceAccess->getId() . ' - ' . $serviceAccess->getName() . ' : ' . $serviceAccess->getAppKey() . ' ' . ($serviceAccess->getActive() == 1 ? '(active)' : '(inactive)'));
            }
        } else {
            $this->logInfo('No client access found.');
        }
    }

    public function showByIdAction(int $id = null): void
    {
        $serviceAccess = ServiceAccessService::findClientById($id);
        if ($serviceAccess) {
            $this->logInfo('Client access entry found:');
            $this->outLine('id:             ' . $serviceAccess->getId());
            $this->outLine('name:           ' . $serviceAccess->getName());
            $this->outLine('description:    ' . $serviceAccess->getDescription());
            $this->outLine('appKey:         ' . $serviceAccess->getAppKey());
            $this->outLine('appSecret:      ' . $serviceAccess->getAppSecret());
            $this->outLine('active:         ' . ($serviceAccess->getActive() == 1 ? 'active' : 'inactive'));
            $this->outLine('updated:        ' . $serviceAccess->getUpdated());
            $this->outLine('created:        ' . $serviceAccess->getCreated());
            $this->outLine('values:');
            foreach ($serviceAccess->getValues() as $value) {
                $this->outLine('                 (' . $value->getId() . ') ' . $value->getName() . ' = ' . $value->getContent());
            }
        } else {
            $this->logInfo('Client access entry not found.');
        }
    }

    public function addClientAction(string $name): void
    {
        list($valid, $model) = ServiceAccessService::addClient($name)->asArray();
        if ($valid) {
            $this->outLine('Access created');
            $this->showByIdAction($model->getId());
        } else {
            $this->outLine('Access NOT created');
        }
    }

    public function removeByIdAction(int $id): void
    {
        $serviceAccess = ServiceAccessService::findClientById($id);
        if ($serviceAccess) {
            $this->logInfo('Client access entry found. Removing...');
            if (ServiceAccessService::removeClient($serviceAccess)) {
                $this->logInfo('... removed.');
            } else {
                $this->logError('Could not remove the client acccess entry.');
            }
        } else {
            $this->logInfo('Client access entry not found.');
        }
    }

    public function addValueAction(int $serviceAccessId, string $name, string $content): void
    {
        $serviceAccess = ServiceAccessService::findClientById($serviceAccessId);
        if ($serviceAccess) {
            list($valid, $model) = ServiceAccessValuesService::addValueToServiceAccess($serviceAccess, $name, $content)->asArray();
            if ($valid) {
                $this->outLine('Value added');
                $this->showByIdAction($serviceAccess->getId());
            } else {
                $this->outLine('Value NOT added');
            }
        } else {
            $this->logInfo('Client access entry not found.');
        }
    }

    public function updateValueAction(int $serviceAccessValueId, string $content): void
    {
        $serviceAccessValue = ServiceAccessValuesService::findById($serviceAccessValueId);
        if ($serviceAccessValue) {
            list($valid, $model) = ServiceAccessValuesService::updateValue($serviceAccessValue, $content)->asArray();
            if ($valid) {
                $this->outLine('Value updated');
            } else {
                $this->outLine('Value NOT updated');
            }
        } else {
            $this->logInfo('Client access value entry not found.');
        }
    }

    public function removeValueAction(int $serviceAccessValueId): void
    {
        $serviceAccessValue = ServiceAccessValuesService::findById($serviceAccessValueId);
        if ($serviceAccessValue) {
            if (ServiceAccessValuesService::removeValue($serviceAccessValue)) {
                $this->outLine('Value removed');
            } else {
                $this->outLine('Value NOT removed');
            }
        } else {
            $this->logInfo('Client access value entry not found.');
        }
    }
}
