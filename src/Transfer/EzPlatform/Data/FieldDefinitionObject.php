<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;

/**
 * Content type object.
 */
class FieldDefinitionObject
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    public $type = 'ezstring';

    /**
     * @var string
     */
    public $fieldGroup = 'content';

    /**
     * @var int
     */
    public $position;

    /**
     * @var bool
     */
    public $isRequired = false;

    /**
     * @var bool
     */
    public $isTranslatable = true;

    /**
     * @var bool
     */
    public $isSearchable = true;

    /**
     * @var bool
     */
    public $isInfoCollector = false;

    /**
     * @var string
     */
    public $defaultValue;

    /**
     * @var array
     */
    protected $names = array();

    /**
     * @var array
     */
    protected $descriptions = array();

    /**
     * FieldDefinitionObject constructor.
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Converts a string to one or more words
     *  'name' -> 'Name'
     *  'short_description' -> 'Short Description'.
     *
     * @param string $string
     *
     * @return string
     */
    protected function stringToReadable($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    /**
     * @param string $description
     * @param string $languageCode
     */
    public function addDescription($description, $languageCode = 'eng-GB')
    {
        $this->descriptions[$languageCode] = $description;
    }

    /**
     * @param array $descriptions
     */
    public function setDescriptions(array $descriptions)
    {
        $this->descriptions = $descriptions;
    }

    /**
     * @return array
     */
    public function getDescriptions()
    {
        return $this->descriptions;
    }

    /**
     * @param string $name
     * @param string $languageCode
     */
    public function addName($name, $languageCode = 'eng-GB')
    {
        $this->names[$languageCode] = $name;
    }

    /**
     * @param array $names
     */
    public function setNames(array $names)
    {
        $this->names = $names;
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return $this->names;
    }

    /**
     * @param FieldDefinitionCreateStruct $fieldDefinitionCreateStruct
     */
    public function fillFieldDefinitionCreateStruct(FieldDefinitionCreateStruct &$fieldDefinitionCreateStruct)
    {
        $fieldDefinitionCreateStruct->names = $this->getNames();
        $fieldDefinitionCreateStruct->descriptions = $this->getDescriptions();
        $fieldDefinitionCreateStruct->fieldGroup = $this->fieldGroup;
        $fieldDefinitionCreateStruct->position = $this->position;
        $fieldDefinitionCreateStruct->isTranslatable = $this->isTranslatable;
        $fieldDefinitionCreateStruct->isRequired = $this->isRequired;
        $fieldDefinitionCreateStruct->isInfoCollector = $this->isInfoCollector;
        $fieldDefinitionCreateStruct->isSearchable = $this->isSearchable;
    }

    /**
     * @param FieldDefinitionUpdateStruct $fieldDefinitionUpdateStruct
     */
    public function fillFieldDefinitionUpdateStruct(FieldDefinitionUpdateStruct &$fieldDefinitionUpdateStruct)
    {
        $fieldDefinitionUpdateStruct->names = $this->getNames();
        $fieldDefinitionUpdateStruct->descriptions = $this->getDescriptions();
        $fieldDefinitionUpdateStruct->fieldGroup = $this->fieldGroup;
        $fieldDefinitionUpdateStruct->position = $this->position;
        $fieldDefinitionUpdateStruct->isTranslatable = $this->isTranslatable;
        $fieldDefinitionUpdateStruct->isRequired = $this->isRequired;
        $fieldDefinitionUpdateStruct->isInfoCollector = $this->isInfoCollector;
        $fieldDefinitionUpdateStruct->isSearchable = $this->isSearchable;
    }
}
