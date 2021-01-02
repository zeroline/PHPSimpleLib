<?php

namespace PHPSimpleLib\Modules\GlobalConfiguration\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;

class SectionFieldModel extends DatabaseAbstractionModel
{
    protected $tableName = "sectionfield";

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
        'sectionid' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
        ),
        'identifier' => array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::STR_MAX => array(255),
        ),
    );

    protected $fieldsForValidationScopes = array();

    public function getSectionId(): int
    {
        return $this->sectionid;
    }

    public function setSectionId(int $sectionid): void
    {
        $this->sectionid = $sectionid;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function setIdentifier(string $identifier): void
    {
        $this->identifier = $identifier;
    }

    public function getSection(): ?SectionModel
    {
        return SectionModel::findOneById($this->getSectionId());
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    public function getTypeInformation(): string
    {
        return $this->typeinformation;
    }

    public function setTypeInformation(?string $typeInformation): void
    {
        $this->typeinformation = $typeInformation;
    }
}
