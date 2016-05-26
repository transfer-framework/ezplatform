<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values;

use Transfer\EzPlatform\Repository\Values\Mapper\ContentTypeMapper;

/*

** Available keys: **

    $data = [
        identifier               => string
        contenttype_groups       => string[]
        name                     => string
        names                    => string[]
        description              => string
        descriptions             => string[]
        name_schema              => string
        url_alias_schema         => string
        is_container             => bool
        default_always_available => bool
        default_sort_field       => int 1-12 Location::SORT_FIELD_*
        default_sort_order       => int 0-1  Location::SORT_ORDER_DESC/SORT_ORDER_ASC
        fields                   => FieldDefinitionObject[] {@link see FieldDefinitionObject}
    ],
    $properties = [
        id                       => int
        content_type_groups      => \eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup[]
        action                   => int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
    ]


** Required on `create`:
**** Required by transfer:
    A unique `identifier`
    Atleast one field in `fields`

**** Required by eZ:
    A unique `identifier`
    Atleast one `contenttype_groups` item
    Atleast one field in `fields`

** Required on `update`:
**** Required by transfer:
    An existing `identifier` and atleast one field in `fields`

**** Required by eZ:
    A unique `identifier`, and atleast one `contenttype_groups` item

*/

/**
 * Content type object.
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
        $this->sortFieldDefinitionsByPosition();
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
        $this->data['fields'][] = new FieldDefinitionObject($identifier, $this, $fieldDefinitionObject);
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
                $this->data[$schema] = sprintf('<%s>', $this->data['fields'][0]->data['identifier']);
            }
        }
    }

    /**
     * Makes sure all fieldDefinition positions are set,
     * and consist of only unique values.
     */
    private function sortFieldDefinitionsByPosition()
    {
        if (!$this->notSetOrEmpty($this->data, 'fields')) {
            usort($this->data['fields'], function ($a, $b) {
                return
                    (isset($a->data['position']) ? $a->data['position'] : 100)
                    >
                    (isset($b->data['position']) ? $b->data['position'] : 100);
            });

            $priority = 10;
            foreach ($this->data['fields'] as $field) {
                $field->data['position'] = $priority;
                $priority += 5;
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
