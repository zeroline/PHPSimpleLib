<?php

namespace PHPSimpleLib\Modules\GlobalConfiguration\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class SectionModel extends DatabaseAbstractionModel
{
    protected $tableName = "section";

    public function __construct($data = null)
    {
        parent::__construct($data);

        $this->addAutomaticField('updated', function ($model) {
            $model->updated = date("Y-m-d H:i:s");
        });
        $this->addAutomaticField('created', function ($model) {
            if ($model->isNew()) {
                $model->created = date("Y-m-d H:i:s");
            }
        });
    }

    protected $ignoreFieldsOnSerialization = array(

    );

    protected $fieldsForValidation = array(
        'sectorid' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'identifier' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
    );

    protected $fieldsForValidationScopes = array();

    public function getSectorId(): int
    {
        return $this->sectorid;
    }

    public function setSectorId(int $sectorId): void
    {
        $this->sectorid = $sectorId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getSector(): ?SectorModel
    {
        return SectorModel::findOneById($this->getSectorId());
    }

    public function getFields(): array
    {
        return SectionFieldModel::repository()->where('sectionid', $this->getId())->read();
    }
}
