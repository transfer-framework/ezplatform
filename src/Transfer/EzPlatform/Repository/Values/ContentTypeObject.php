<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values;

use Transfer\EzPlatform\Repository\Values\Mapper\ContentTypeMapper;

/**
 * Content type object.
 *
 * @see http://transfer-framework.com/docs/1.0/sources_and_targets/ezplatform/the_objects/contenttypeobject.html
 */
class ContentTypeObject extends EzPlatformObject
{
    /**
     * @var ContentTypeMapper
     */
    private $mapper;

    /**
     * {@inheritdoc}
     */
    public function __construct($data, $properties = array())
    {
        parent::__construct($data, $properties);

        if (isset($data['fields'])) {
            $this->setFieldDefinitions($data['fields']);
        }

        $this->setMissingDefaults();
    }

    /**
     * Values in array must be of type Location, LocationObject or int.
     *
     * @param array $fieldDefinitionObjects
     */
    public function setFieldDefinitions(array $fieldDefinitionObjects)
    {
        $this->data['fields'] = [];
        foreach ($fieldDefinitionObjects as $identifier => $fieldDefinitionObject) {
            $this->addFieldDefinitionObject($identifier, $fieldDefinitionObject);
        }
    }

    /**
     * Convert parameters to FieldDefinitionObject and stores it on the ContentTypeObject.
     *
     * @param string $identifier
     * @param $fieldDefinitionObject
     *
     * @internal param array|FieldDefinitionObject $fieldDefinition
     */
    public function addFieldDefinitionObject($identifier, $fieldDefinitionObject)
    {
        $this->data['fields'][$identifier] = new FieldDefinitionObject($identifier, $this, $fieldDefinitionObject);
    }

    /**
     * Build default values.
     */
    private function setMissingDefaults()
    {
        if ($this->notSetOrEmpty($this->data, 'names')) {
            $this->data['names'] = array(
                $this->data['main_language_code'] => $this->identifierToReadable($this->data['identifier']),
            );
        }

        foreach (array('name_schema', 'url_alias_schema') as $schema) {
            if ($this->notSetOrEmpty($this->data, $schema)) {
                $this->data[$schema] = sprintf('<%s>', $this->data['fields'][key($this->data['fields'])]->data['identifier']);
            }
        }
    }

    /**
     * @param array  $array
     * @param string $key
     *
     * @return bool
     */
    private function notSetOrEmpty(array $array, $key)
    {
        return !isset($array[$key]) || empty($array[$key]);
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

    /**
     * @return array
     */
    public function getLanguageCodes()
    {
        return array_unique(
            array_merge(
                array($this->data['main_language_code']),
                array_keys($this->data['names']),
                array_keys($this->data['descriptions'])
            )
        );
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
