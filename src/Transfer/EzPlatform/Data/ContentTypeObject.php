<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Content\ContentTypeMapper;

/**
 * Content type object.
 */
class ContentTypeObject extends ValueObject
{

    /**
     * @var ContentTypeMapper
     */
    private $mapper;

    /**
     * @var FieldDefinitionObject[]
     */
    public $fields;

    /**
     * @inheritdoc
     */
    public function __construct($identifier, $data)
    {
        $data['identifier'] = $identifier;
        parent::__construct($data);
        foreach($data['fields'] as $fieldIdentifier => $field) {
            $this->fields[] = new FieldDefinitionObject($fieldIdentifier, $this, $field);
        }
        unset($data['fields']);
        $this->setMissingDefaults();
    }

    private function setMissingDefaults()
    {
        if($this->notSetOrEmpty($this->data, 'names')) {
            $this->data['names'] = array(
                $this->data['main_language_code'] => $this->identifierToReadable($this->data['identifier'])
            );
        }

        if($this->notSetOrEmpty($this->data, 'name_schema')) {
            $this->data['name_schema'] = sprintf('<%s>', $this->fields[0]->data['identifier']);
        }

        if($this->notSetOrEmpty($this->data, 'url_alias_schema')) {
            $this->data['url_alias_schema'] = sprintf('<%s>', $this->fields[0]->data['identifier']);
        }
    }

    /**
     * @param array $array
     * @param string $key
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
