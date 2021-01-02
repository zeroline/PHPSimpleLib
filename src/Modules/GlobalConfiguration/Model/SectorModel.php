<?php

namespace PHPSimpleLib\Modules\GlobalConfiguration\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class SectorModel extends DatabaseAbstractionModel
{
    protected $tableName = "sector";

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
        'identifier' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
    );

    protected $fieldsForValidationScopes = array();

    public function getSectorSchemaId(): int
    {
        return $this->sectorschemaid;
    }

    public function setSectorSchemaId(int $sectorId): void
    {
        $this->sectorschemaid = $sectorId;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getSchema(): ?string
    {
        return $this->validationschema;
    }

    public function setSchema(?string $schema): void
    {
        $this->validationschema = $schema;
    }

    public function getSchemaArray(): array
    {
        return json_decode($this->getSchema(), true);
    }

    public function getSections(): array
    {
        return SectionModel::repository()->where('sectorid', $this->getId())->read();
    }
}
