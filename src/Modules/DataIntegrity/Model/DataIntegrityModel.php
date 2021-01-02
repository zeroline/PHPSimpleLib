<?php

namespace PHPSimpleLib\Modules\DataIntegrity\Model;

use PHPSimpleLib\Core\Data\DatabaseAbstractionModel;
use PHPSimpleLib\Core\Data\EnumValidatorRules;
use PHPSimpleLib\Modules\DataIntegrity\Lib\EnumEntryState;

class DataIntegrityModel extends DatabaseAbstractionModel
{
    public function __construct($data = null)
    {
        $this->fieldsForValidation['activeState' ] = array(
            EnumValidatorRules::REQUIRED => array(),
            EnumValidatorRules::IS_NUMBER => array(),
            EnumValidatorRules::IN_ARRAY => array(
                array(EnumEntryState::ACTIVE, EnumEntryState::DELETED, EnumEntryState::INACITVE, EnumEntryState::REQUEST_FOR_PERM_DELETION)
            ),
        );

        $this->addAutomaticField('updated', function ($model) {
            $model->updated = date("Y-m-d H:i:s");
        });
        $this->addAutomaticField('created', function ($model) {
            if ($model->isNew()) {
                $model->created = date("Y-m-d H:i:s");
            }
        });

        parent::__construct($data);
    }

    /**
     * Returns the active state
     *
     * @return integer
     */
    public function getActiveState() : int
    {
        return $this->activeState;
    }

    /**
     * Indicates if the entry is active
     *
     * @return boolean
     */
    public function getIsActive() : bool
    {
        return ($this->getActiveState() === EnumEntryState::ACTIVE);
    }

    /**
     * Indicates if the entry is inactive
     *
     * @return boolean
     */
    public function getIsInActive() : bool
    {
        return ($this->getActiveState() === EnumEntryState::INACITVE);
    }

    /**
     * Indicates if the entry is marked a deleted
     *
     * @return boolean
     */
    public function getIsDeleted() : bool
    {
        return ($this->getActiveState() === EnumEntryState::DELETED);
    }

    /**
     * Indicates if the entry is marked for real deletion
     *
     * @return boolean
     */
    public function getIsMarkedForPermanentDeletion() : bool
    {
        return ($this->getActiveState() === EnumEntryState::REQUEST_FOR_PERM_DELETION);
    }

    /**
     *
     * @return void
     */
    public function markAsActive() : void
    {
        $this->activeState = EnumEntryState::ACTIVE;
    }

    /**
     *
     * @return void
     */
    public function markAsInActive() : void
    {
        $this->activeState = EnumEntryState::INACITVE;
    }

    /**
     *
     * @return void
     */
    public function markAsDeleted() : void
    {
        $this->activeState = EnumEntryState::DELETED;
    }

    /**
     *
     * @return void
     */
    public function markAsMarkedForPermanentDeletion() : void
    {
        $this->activeState = EnumEntryState::REQUEST_FOR_PERM_DELETION;
    }
}
