<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Worker\Transformer;

use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\Worker\WorkerInterface;

/**
 * Transforms array to Transfer eZ Platform Content Type object.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ArrayToEzPlatformContentTypeObjectTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($array)
    {
        $this->isValid($array);

        foreach ($array as $identifier => $contenttype) {
            $ct = new ContentTypeObject($identifier);

            foreach ($contenttype as $key => $attribute) {
                switch ($key) {
                    case 'main_language_code':
                        $ct->mainLanguageCode = $attribute;
                        break;

                    case 'contenttype_groups':
                        $ct->setContentTypeGroups($attribute);
                        break;

                    case 'contenttype_group':
                        $ct->addContentTypeGroup($attribute);
                        break;

                    case 'names':
                        $ct->setNames($attribute);
                        break;

                    case 'name':
                        $ct->addName($attribute);
                        break;

                    case 'descriptions':
                        $ct->setDescriptions($attribute);
                        break;

                    case 'description':
                        $ct->addDescription($attribute);
                        break;

                    case 'name_schema':
                        $ct->nameSchema = $attribute;
                        break;

                    case 'url_alias_schema':
                        $ct->urlAliasSchema = $attribute;
                        break;

                    case 'is_container':
                        $ct->isContainer = $attribute;
                        break;

                    case 'fields':
                        foreach ($this->getFieldDefinitions($attribute) as $fd) {
                            $ct->addFieldDefinition($fd);
                        }
                        break;
                }
            }

            return $ct;
        }

        return;
    }

    /**
     * @param array $fieldDefinitions
     *
     * @return FieldDefinitionObject[]
     */
    private function getFieldDefinitions($fieldDefinitions)
    {
        $cts = array();
        $positions = array(0);

        foreach ($fieldDefinitions as $fieldIdentifier => $fieldDefinition) {
            $fd = $this->getFieldDefinitionFromData($fieldIdentifier, $fieldDefinition);

            if (null === $fd->position) {
                $fd->position = max($positions) + 10;
            }
            $positions[] = $fd->position;

            $cts[] = $fd;
        }

        return $cts;
    }

    /**
     * @param string     $fieldIdentifier
     * @param array|null $fieldDefinitionData
     *
     * @return FieldDefinitionObject
     */
    private function getFieldDefinitionFromData($fieldIdentifier, $fieldDefinitionData = null)
    {
        $fd = new FieldDefinitionObject($fieldIdentifier);
        if (!is_array($fieldDefinitionData) || empty($fieldDefinitionData)) {
            return $fd;
        }

        foreach ($fieldDefinitionData as $key => $value) {
            switch ($key) {
                case 'type':
                    $fd->type = $value;
                    break;

                case 'names':
                    $fd->setNames($value);
                    break;

                case 'name':
                    $fd->addName($value);
                    break;

                case 'descriptions':
                    $fd->setDescriptions($value);
                    break;

                case 'description':
                    $fd->addDescription($value);
                    break;

                case 'field_group':
                    $fd->fieldGroup = $value;
                    break;

                case 'position':
                    $fd->position = $value;
                    break;

                case 'default_value':
                    $fd->defaultValue = $value;
                    break;

                case 'is_translatable':
                    $fd->isTranslatable = $value;
                    break;

                case 'is_required':
                    $fd->isRequired = $value;
                    break;

                case 'is_searchable':
                    $fd->isSearchable = $value;
                    break;

                case 'is_info_collector':
                    $fd->isInfoCollector = $value;
                    break;

            }
        }

        return $fd;
    }

    /**
     * Throws exceptions on error.
     *
     * @param array $array
     *
     * @return bool
     *
     * @throws \InvalidArgumentException
     * @throws InvalidDataStructureException
     */
    private function isValid($array)
    {
        if (!is_array($array)) {
            throw new \InvalidArgumentException(
                sprintf('Expected argument #1 to be of type array, got %s', gettype($array))
            );
        }

        return $this->hasValidFields($array) && $this->hasValidIdentifiers(array_keys($array));
    }

    /**
     * @param $array
     *
     * @return bool
     *
     * @throws InvalidDataStructureException
     */
    private function hasValidFields($array)
    {
        foreach ($array as $identifier => $contenttype) {
            if (!array_key_exists('fields', $contenttype) || count($contenttype['fields']) == 0) {
                throw new InvalidDataStructureException(
                    sprintf('Atleast one field must be defined for identifier "%s".', $identifier)
                );
            }
        }

        return true;
    }

    /**
     * @param string[] $identifiers
     *
     * @return bool
     *
     * @throws InvalidDataStructureException
     */
    private function hasValidIdentifiers($identifiers)
    {
        foreach ($identifiers as $identifier) {
            if (!is_string($identifier)) {
                throw new InvalidDataStructureException(
                    sprintf('Expected identifier to be of type string, got "%s".', gettype($identifier))
                );
            }
        }

        return true;
    }
}
