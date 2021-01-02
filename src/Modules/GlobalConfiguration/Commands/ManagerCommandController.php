<?php

namespace PHPSimpleLib\Modules\GlobalConfiguration\Commands;

use PHPSimpleLib\Core\Controlling\CliController;
use PHPSimpleLib\Modules\GlobalConfiguration\Model\SectorModel;
use PHPSimpleLib\Modules\GlobalConfiguration\Service\ConfigurationService;

class ManagerCommandController extends CliController
{
    public function overviewAction(): void
    {
        $sectors = SectorModel::repository()->read();
        foreach ($sectors as $sector) {
            $this->outLine('================================');
            $this->outLine($sector->getIdentifier());
            foreach ($sector->getSections() as $section) {
                $this->outLine("\t" . $section->getIdentifier());
                foreach ($section->getFields() as $field) {
                    $this->outLine("\t\t" . $field->getIdentifier() . "\t" . $field->getContent());
                }
            }
        }
    }

    public function updateAction(string $sectorIdentifier, string $sectionIdentifier, string $fieldIdentifier, string $value): void
    {
        if (ConfigurationService::setConfig($sectorIdentifier, $sectionIdentifier, $fieldIdentifier, $value)) {
            $this->outLine('Field updated');
        } else {
            $this->outLine('Update failed');
        }
    }

    public function deleteSectorAction(string $sectorIdentifier): void
    {
        if (ConfigurationService::deleteSectorByIdentifier($sectorIdentifier)) {
            $this->outLine('Sector removed');
        } else {
            $this->outLine('Sector could not be removed');
        }
    }

    public function deleteSectionAction(string $sectorIdentifier, string $sectionIdentifier): void
    {
        if (ConfigurationService::deleteSectionByIdentifier($sectorIdentifier, $sectionIdentifier)) {
            $this->outLine('Section removed');
        } else {
            $this->outLine('Section could not be removed');
        }
    }

    public function deleteFieldAction(string $sectorIdentifier, string $sectionIdentifier, string $fieldIdentifier): void
    {
        if (ConfigurationService::deleteFieldByIdentifier($sectorIdentifier, $sectionIdentifier, $fieldIdentifier)) {
            $this->outLine('Field removed');
        } else {
            $this->outLine('Field could not be removed');
        }
    }
}
