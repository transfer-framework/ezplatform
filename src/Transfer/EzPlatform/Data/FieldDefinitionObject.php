<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Mapper\FieldDefinitionMapper;

/**
 * Content type object.
 */
class FieldDefinitionObject extends ValueObject
{
    /**
     * @var ContentTypeObject
     */
    private $parent;

    /**
     * @var FieldDefinitionMapper
     */
    private $mapper;

    /**
     * {@inheritdoc}
     */
    public function __construct($identifier, ContentTypeObject $parent, $data = array())
    {
        $data['identifier'] = $identifier;
        $this->parent = &$parent;
        parent::__construct($data);
        $this->setMissingDefaults();
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
    protected function identifierToReadable($string)
    {
        return ucwords(str_replace('_', ' ', $string));
    }

    /**
     * @return FieldDefinitionMapper
     */
    public function getMapper()
    {
        if (!$this->mapper) {
            $this->mapper = new FieldDefinitionMapper($this);
        }

        return $this->mapper;
    }

    private function setMissingDefaults()
    {
        if (!isset($this->data['names']) || empty($this->data['names'])) {
            $this->data['names'] = array(
                $this->parent->data['main_language_code'] => $this->identifierToReadable($this->data['identifier']),
            );
        }
    }
}
