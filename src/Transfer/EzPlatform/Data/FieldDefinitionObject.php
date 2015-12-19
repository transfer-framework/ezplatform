<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data;

use Transfer\EzPlatform\Repository\Content\FieldDefinitionRepository;

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
     * @var FieldDefinitionRepository
     */
    protected $repository;

    /**
     * FieldDefinitionObject constructor.
     *
     * @param string $identifier
     */
    public function __construct($identifier)
    {
        $this->identifier = $identifier;
        $this->names = array('eng-GB' => $this->identifierToReadable($identifier));
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
    protected function identifierToReadable($string)
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
     * @return FieldDefinitionRepository
     */
    public function getRepository()
    {
        if (!$this->repository) {
            $this->repository = new FieldDefinitionRepository($this);
        }

        return $this->repository;
    }
}
