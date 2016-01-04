<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Repository\Content\ContentTypeMapper;

/**
 * Content type object.
 */
class ContentTypeObject
{
    /**
     * @var string
     */
    protected $identifier;

    /**
     * @var string
     */
    public $mainLanguageCode = 'eng-GB';

    /**
     * @var array
     */
    protected $contentTypeGroups = array('Content');

    /**
     * @var array
     */
    protected $names = array();

    /**
     * @var array
     */
    protected $descriptions = array();

    /**
     * @var string
     */
    public $nameSchema;

    /**
     * @var string
     */
    public $urlAliasSchema;

    /**
     * @var bool
     */
    public $isContainer = true;

    /**
     * @var bool
     */
    public $defaultAlwaysAvailable = true;

    /**
     * Valid values are found at {@link Location::SORT_FIELD_*}.
     *
     * @var int
     */
    public $defaultSortField = Location::SORT_FIELD_NAME;

    /**
     * Valid values are found at {@link Location::SORT_ORDER_*}.
     *
     * @var int
     */
    public $defaultSortOrder = Location::SORT_ORDER_ASC;

    /**
     * @var FieldDefinitionObject[]
     */
    protected $fieldDefinitions = array();

    /**
     * @var ContentTypeMapper
     */
    protected $mapper;

    /**
     * ContentTypeObject constructor.
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        $this->names = array($this->mainLanguageCode => $this->identifierToReadable($identifier));
    }

    /**
     * @return string
     */
    public function getMainGroupIdentifier()
    {
        return $this->contentTypeGroups[0];
    }

    /**
     * Converts an identifier to one or more words
     *  'name' -> 'Name'
     *  'short_description' -> 'Short Description'.
     *
     * @param string $identifier
     *
     * @return string
     */
    protected function identifierToReadable($identifier)
    {
        return ucwords(str_replace('_', ' ', $identifier));
    }

    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * @param string $contentTypeGroup
     */
    public function addContentTypeGroup($contentTypeGroup)
    {
        if (!in_array($contentTypeGroup, $this->contentTypeGroups)) {
            $this->contentTypeGroups[] = $contentTypeGroup;
        }
    }

    /**
     * @param array $contentTypeGroups
     */
    public function setContentTypeGroups(array $contentTypeGroups)
    {
        $this->contentTypeGroups = $contentTypeGroups;
    }

    /**
     * @return array
     */
    public function getContentTypeGroups()
    {
        return $this->contentTypeGroups;
    }

    /**
     * @param string $name
     * @param string $languageCode
     */
    public function addName($name, $languageCode = null)
    {
        $languageCode = $languageCode !== null ? $languageCode : $this->mainLanguageCode;
        $this->names[$languageCode] = $name;
    }

    /**
     * @param array $names array('eng-GB'=>'My Name')
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
     * @param string $description
     * @param string $languageCode
     */
    public function addDescription($description, $languageCode = null)
    {
        $languageCode = $languageCode !== null ? $languageCode : $this->mainLanguageCode;
        $this->descriptions[$languageCode] = $description;
    }

    /**
     * @param array $descriptions array('eng-GB' => 'My description')
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
     * @return array
     */
    public function getLanguageCodes()
    {
        return array_unique(
            array_merge(
                array($this->mainLanguageCode),
                array_keys($this->names),
                array_keys($this->descriptions)
            )
        );
    }

    /**
     * @param FieldDefinitionObject $field
     */
    public function addFieldDefinition(FieldDefinitionObject $field)
    {
        if (count($this->fieldDefinitions) > 0) {
            foreach ($this->fieldDefinitions as $index => $fieldDefinition) {
                if ($fieldDefinition->getIdentifier() == $field->getIdentifier()) {
                    $this->fieldDefinitions[$index] = $field;

                    return;
                }
            }
        } else {
            if (empty($this->nameSchema)) {
                $this->nameSchema = sprintf('<%s>', $field->getIdentifier());
            }
            if (empty($this->urlAliasSchema)) {
                $this->urlAliasSchema = sprintf('<%s>', $field->getIdentifier());
            }
        }
        $this->fieldDefinitions[] = $field;
    }

    /**
     * @param FieldDefinitionObject[] $fields
     */
    public function setFieldDefinitions(array $fields)
    {
        $this->fieldDefinitions = $fields;
    }

    /**
     * @return FieldDefinitionObject[]
     */
    public function getFieldDefinitions()
    {
        return $this->fieldDefinitions;
    }

    /**
     * @return ContentTypeMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new ContentTypeMapper($this);
        }

        return $this->mapper;
    }
}
