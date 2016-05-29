<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values;

use Transfer\EzPlatform\Repository\Values\Mapper\FieldDefinitionMapper;

/*

** Available keys: **

    $parent = Transfer\EzPlatform\Data\ContentTypeObject

    $data = [
        identifer           => string
        type                => string
        names               => string[]
        descriptions        => string[]
        field_group         => string
        position            => int
        is_translatable     => bool
        is_required         => bool
        is_info_collector   => bool
        is_searchable       => bool
    ],
    $properties = [
        <none>
    ]


** Required on `create`:
**** Required by transfer:
    An `identifier` unique to its ContentType(Object)

**** Required by eZ:
    An `identifier` unique to its ContentType
    A type, transfer defaults to ezstring

** Required on `update`:
**** Required by transfer:
    An `identifier`

**** Required by eZ:
    An `identifier`

*/

/**
 * Content type object.
 */
class FieldDefinitionObject extends EzPlatformObject
{
    /**
     * @var ContentTypeObject
     */
    private $contentType;

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
        $this->contentType = &$parent;
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
                $this->contentType->data['main_language_code'] => $this->identifierToReadable($this->data['identifier']),
            );
        }
    }
}
