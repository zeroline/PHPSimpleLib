<?php

namespace PHPSimpleLib\Modules\GlobalConfiguration\Service;

use PHPSimpleLib\Modules\GlobalConfiguration\Model\SectorSchemaModel;
use PHPSimpleLib\Modules\GlobalConfiguration\Model\SectorModel;
use PHPSimpleLib\Modules\GlobalConfiguration\Model\SectionModel;
use PHPSimpleLib\Modules\GlobalConfiguration\Model\SectionFieldModel;
use PHPSimpleLib\Core\Data\ValidatedModel;

class ConfigurationService
{

    /**
     * Checks if the named sector exists
     *
     * @param string $identifier
     * @return boolean
     */
    private static function sectorExists(string $identifier) : bool
    {
        return (SectorModel::repository()->where('identifier', $identifier)->count() === 1);
    }

    /**
     * Checks if the named sections exists within the given sector
     *
     * @param SectorModel $sector
     * @param string $identifier
     * @return boolean
     */
    private static function sectionExists(SectorModel $sector, string $identifier) : bool
    {
        return (SectionModel::repository()->where('identifier', $identifier)->where('sectorid', $sector->getId())->count() === 1);
    }

    /**
     * Checks if the named field exists within the given section
     *
     * @param SectionModel $section
     * @param string $identifier
     * @return boolean
     */
    private static function sectionFieldExists(SectionModel $section, string $identifier) : bool
    {
        return (SectionFieldModel::repository()->where('identifier', $identifier)->where('sectionid', $section->getId())->count() === 1);
    }

    /**
     * Returns a sector found by the given name if found
     *
     * @param string $identifier
     * @return SectorModel|null
     */
    private static function getSectorByIdentifier(string $identifier) : ?SectorModel
    {
        return SectorModel::repository()->where('identifier', $identifier)->readOne();
    }

    /**
     * Returns a section found by the given name and sector if found
     *
     * @param SectorModel $sector
     * @param string $identifier
     * @return SectionModel|null
     */
    private static function getSectionByIdentifier(SectorModel $sector, string $identifier) : ?SectionModel
    {
        return SectionModel::repository()->where('identifier', $identifier)->where('sectorid', $sector->getId())->readOne();
    }

    /**
     * Returns a field found by the given name and section if found
     *
     * @param SectionModel $section
     * @param string $identifier
     * @return SectionFieldModel|null
     */
    private static function getFieldByIdentifier(SectionModel $section, string $identifier) : ?SectionFieldModel
    {
        return SectionFieldModel::repository()->where('identifier', $identifier)->where('sectionid', $section->getId())->readOne();
    }

    /**
     * Creates a sector
     *
     * @param string $identifier
     * @param string|null $schema
     * @return void
     */
    public static function createSector(string $identifier, ?string $schema = null)
    {
        $model = new SectorModel(array(
            'identifier' => $identifier,
            'validationschema' => $schema
        ));

        if ($model->isValid()) {
            return $model->save();
        }

        return false;
    }

    /**
     * Creates a section
     *
     * @param SectorModel $sector
     * @param string $identifier
     * @return boolean
     */
    public static function createSection(SectorModel $sector, string $identifier) : bool
    {
        $model = new SectionModel(array(
            'identifier' => $identifier,
            'sectorid' => $sector->getId()
        ));

        if ($model->isValid()) {
            return $model->save();
        }

        return false;
    }

    /**
     * Creates a field or updates one if it already exists
     *
     * @param SectionModel $section
     * @param string $identifier
     * @param string|null $content
     * @param string|null $typeinformation
     * @return boolean
     */
    private static function createOrUpdateField(SectionModel $section, string $identifier, ?string $content = null, ?string $typeinformation = null) : bool
    {
        $model = null;
        if (self::sectionFieldExists($section, $identifier)) {
            $model = self::getFieldByIdentifier($section, $identifier);
            if ($model) {
                $model->setTypeInformation($typeinformation);
                $model->setContent($content);
            }
        } else {
            $model = new SectionFieldModel(array(
                'sectionid' => $section->getId(),
                'identifier' => $identifier,
                'typeinformation' => $typeinformation,
                'content' => $content
            ));
        }

        if ($model) {
            $sector = $section->getSector();
            if (!is_null($sector->getSchema()) && !empty($sector->getSchema())) {
                if (self::validateSectionIdentifierAgainstSchema($sector, $identifier, $content)) {
                    return $model->save();
                }
            } else {
                return $model->save();
            }
        }

        return false;
    }

    /**
     * Validation
     *
     * @param SectorModel $sector
     * @param string $identifier
     * @return boolean
     */
    private static function validateSectionIdentifierAgainstSchema(SectorModel $sector, string $identifier) : bool
    {
        if (!is_null($sector->getSchema()) && !empty($sector->getSchema())) {
            $schemaArray = $sector->getSchemaArray();
            if (array_key_exists($identifier, $schemaArray)) {
                return true;
            } else {
                return false;
            }
        } else {
            return true; // True because no schema is present
        }
    }

    /**
     * Validation
     *
     * @param SectionModel $section
     * @param string $identifier
     * @param string|null $content
     * @return boolean
     */
    private static function validateFieldAgainstSchema(SectionModel $section, string $identifier, ?string $content) : bool
    {
        $sector = $section->getSector();
        if (!is_null($sector->getSchema()) && !empty($sector->getSchema())) {
            $schemaArray = $sector->getSchemaArray();
            if (array_key_exists($section->getIdentifier(), $schemaArray)) {
                $schemaSubArray = $schemaArray[$section->getIdentifier()];
                if (array_key_exists($identifier, $schemaSubArray)) {
                    $schemaFieldSubArray = $schemaSubArray[$identifier];
                    $validatedModel = new ValidatedModel(array(
                        $identifier => $content
                    ));
                    $validatedModel->setFieldsForValidation($schemaFieldSubArray);

                    return $validatedModel->isValid();
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            return true; // True because no schema is present
        }
    }

    /**
     * Returns all config for a given sector
     *
     * @param string $sectorIdentifier
     * @return array
     */
    public static function getSectorConfig(string $sectorIdentifier) : array
    {
        $result = array();

        $sector = self::getSectorByIdentifier($sectorIdentifier);
        if ($sector) {
            foreach ($sector->getSections() as $section) {
                $result[$section->getIdentifier()] = array();
                foreach ($section->getFields() as $field) {
                    $result[$section->getIdentifier()][$field->getIdentifier()] = $field->getContent();
                }
            }
        }

        return $result;
    }

    
    /**
     * Returns a specific field model if found.
     *
     * @param string $sectorIdentifier
     * @param string $sectionIdentifier
     * @param string $fieldIdentifier
     * @return SectionFieldModel|null
     */
    public static function getField(string $sectorIdentifier, string $sectionIdentifier, string $fieldIdentifier) : ?SectionFieldModel
    {
        if (self::sectorExists($sectorIdentifier)) {
            $sector = self::getSectorByIdentifier($sectorIdentifier);
            if (self::sectionExists($sector, $sectionIdentifier)) {
                $section = self::getSectionByIdentifier($sectionIdentifier);
                if (self::sectionFieldExists($section, $fieldIdentifier)) {
                    return self::getFieldByIdentifier($section, $fieldIdentifier);
                }
            }
        }
        return null;
    }

    /**
     * Returns the configurated value if found.
     * Values are stored as string. Further usage or casting has
     * to be done after getting the string value.
     *
     * If no specific field is provided, an array with all fields
     * found in the given section is returned
     *
     * @param string $sectorIdentifier
     * @param string $sectionIdentifier
     * @param string|null $fieldIdentifier
     * @param mixed $fallback
     * @return array|string|mixed|null
     */
    public static function getConfig(string $sectorIdentifier, string $sectionIdentifier, ?string $fieldIdentifier = null, $fallback = null)
    {
        if (self::sectorExists($sectorIdentifier)) {
            $sector = self::getSectorByIdentifier($sectorIdentifier);
            if (self::sectionExists($sector, $sectionIdentifier)) {
                $section = self::getSectionByIdentifier($sector, $sectionIdentifier);
                if (!is_null($fieldIdentifier)) {
                    if (self::sectionFieldExists($section, $fieldIdentifier)) {
                        $field = self::getFieldByIdentifier($section, $fieldIdentifier);
                        return $field->getContent();
                    }
                } else {
                    $fields = $section->getFields();
                    $returnArray = array();
                    foreach ($fields as $field) {
                        $returnArray[$field->getIdentifier()] = $field->getContent();
                    }
                    return $returnArray;
                }
            }
        }
        return $fallback;
    }

    /**
     * Shortend function for getConfig using a config path
     * [SECTORIdentifier].[SECTIONIdentifier].[FIELDIdentifier(Optional)]
     *
     * @param string $path
     * @param mixed $fallback
     * @return mixed
     */
    public static function config(string $path, $fallback = null)
    {
        list($sectorIdentifier, $sectionIdentifier, $fieldIdentifier) = explode('.', $path);
        return self::getConfig($sectorIdentifier, $sectionIdentifier, $fieldIdentifier, $fallback);
    }

    /**
     * Sets or creates a specific configuration field.
     * Please see that if a validation schema is present in
     * the sector and validation fails the method will just return
     * false and no further information what could be wrong.
     *
     * Sectors and section will be created if they not exists. Exceptions
     * will raise if the base creation process fails. No exception will raise if
     * validation fails.
     *
     * @param string $sectorIdentifier
     * @param string $sectionIdentifier
     * @param string $fieldIdentifier
     * @param string|null $content
     * @param string|null $typeInformation
     * @param string|null $schema
     * @return boolean
     */
    public static function setConfig(string $sectorIdentifier, string $sectionIdentifier, string $fieldIdentifier, ?string $content = null, ?string $typeInformation = null, ?string $schema = null) : bool
    {
        if (!self::sectorExists($sectorIdentifier)) {
            if (!self::createSector($sectorIdentifier, $schema)) {
                throw new \Exception('Sector does not exist and cannot be created');
            }
        }

        $sector = self::getSectorByIdentifier($sectorIdentifier);

        if (!self::sectionExists($sector, $sectionIdentifier)) {
            if (self::validateSectionIdentifierAgainstSchema($sector, $sectionIdentifier)) {
                if (!self::createSection($sector, $sectionIdentifier)) {
                    throw new \Exception('Section does not exist and cannot be created');
                }
            }
        }

        $section = self::getSectionByIdentifier($sector, $sectionIdentifier);

        if (self::createOrUpdateField($section, $fieldIdentifier, $content, $typeInformation)) {
            return true;
        }

        return false;
    }

    /**
     * Delete a specific field
     *
     * @param SectionFieldModel $field
     * @return boolean
     */
    public static function deleteField(SectionFieldModel $field) : bool
    {
        return $field->delete();
    }

    /**
     * Delete a specific section
     *
     * @param SectionModel $section
     * @return boolean
     */
    public static function deleteSection(SectionModel $section) : bool
    {
        return $section->delete();
    }

    /**
     * Delete a specific sector
     *
     * @param SectorModel $sector
     * @return boolean
     */
    public static function deleteSector(SectorModel $sector) : bool
    {
        return $sector->delete();
    }

    /**
     * Delete a sector found by the identifier
     *
     * @param string $identifier
     * @return boolean
     */
    public static function deleteSectorByIdentifier(string $identifier) : bool
    {
        if ($sector = self::getSectorByIdentifier($identifier)) {
            return self::deleteSector($sector);
        }
        return false;
    }

    /**
     * Delete a sector found by the identifier
     *
     * @param string $sectorIdentifier
     * @param string $sectionIdentifier
     * @return boolean
     */
    public static function deleteSectionByIdentifier(string $sectorIdentifier, string $sectionIdentifier) : bool
    {
        if ($sector = self::getSectorByIdentifier($sectorIdentifier)) {
            if ($section = self::getSectionByIdentifier($sector, $sectionIdentifier)) {
                return self::deleteSection($section);
            }
        }
        return false;
    }

    /**
     * Delete a field found by the identifier
     *
     * @param string $sectorIdentifier
     * @param string $sectionIdentifier
     * @param string $fieldIdentifier
     * @return boolean
     */
    public static function deleteFieldByIdentifier(string $sectorIdentifier, string $sectionIdentifier, string $fieldIdentifier) : bool
    {
        if ($sector = self::getSectorByIdentifier($sectorIdentifier)) {
            if ($section = self::getSectionByIdentifier($sector, $sectionIdentifier)) {
                if ($field = self::getFieldByIdentifier($section, $fieldIdentifier)) {
                    return self::deleteField($field);
                }
            }
        }
        return false;
    }
}
