<?php

namespace Transfer\EzPlatform\Worker\Transformer;

use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\Worker\WorkerInterface;

/**
 * Class ArrayToEzPlatformContentTypeObjectTransformer.
 */
class ArrayToEzPlatformContentTypeObjectTransformer implements WorkerInterface
{
    /**
     * {@inheritdoc}
     */
    public function handle($array)
    {
        if (!is_array($array)) {
            throw new \InvalidArgumentException(
                sprintf('Expected argument #1 to be of type array, got %s', gettype($array))
            );
        }

        $identifier = key($array);
        $a = $array[$identifier];

        if (!is_string($identifier)) {
            throw new \InvalidArgumentException(
                sprintf('Expected identifier to be of type string, got "%s".', gettype($identifier))
            );
        }
        if (!array_key_exists('fields', $a)) {
            throw new InvalidDataStructureException(
                sprintf('Missing key "%s" in identifier "%s".', 'fields', $identifier)
            );
        }
        if (count($a['fields']) == 0) {
            throw new InvalidDataStructureException(
                sprintf('Atleast one field must be defined for identifier "%s".', $identifier)
            );
        }

        $ct = new ContentTypeObject($identifier);

        if (isset($a['main_language_code'])) {
            $ct->mainLanguageCode = $a['main_language_code'];
        }

        if (isset($a['main_group_identifier'])) {
            $ct->setContentTypeGroups(array($a['main_group_identifier']));
        }

        if (isset($a['contenttype_groups']) && is_array($a['contenttype_groups'])) {
            foreach ($a['contenttype_groups'] as $contenttypeGroup) {
                $ct->addContentTypeGroup($contenttypeGroup);
            }
        }
        if (isset($a['names'])) {
            $ct->setNames($a['names']);
        } elseif ($a['name']) {
            $ct->addName($a['name'], $ct->mainLanguageCode);
        }

        if (isset($a['descriptions'])) {
            $ct->setDescriptions($a['descriptions']);
        } elseif ($a['description']) {
            $ct->addDescription($a['description'], $ct->mainLanguageCode);
        }

        if (isset($ct['name_schema'])) {
            $ct->nameSchema = $a['name_schema'];
        }

        if (isset($ct['url_alias_schema'])) {
            $ct->urlAliasSchema = $a['url_alias_schema'];
        }

        if (isset($ct['container'])) {
            $ct->isContainer = $a['container'];
        }

        $positions = array(0);
        foreach ($a['fields'] as $fieldId => $field) {
            $fieldDefinition = new FieldDefinitionObject($fieldId);

            if (isset($field['type'])) {
                $fieldDefinition->type = $field['type'];
            }

            if (isset($field['names'])) {
                $fieldDefinition->setNames($field['names']);
            } elseif (isset($field['name'])) {
                $fieldDefinition->addName($field['name'], $ct->mainLanguageCode);
            }
            if (isset($field['descriptions'])) {
                $fieldDefinition->setDescriptions($field['descriptions']);
            } elseif ($field['description']) {
                $fieldDefinition->addDescription($field['description'], $ct->mainLanguageCode);
            }
            if (isset($field['field_group'])) {
                $fieldDefinition->fieldGroup = $field['field_group'];
            }
            $position = isset($field['position']) ? $field['position'] : max($positions) + 10;
            $positions[] = $fieldDefinition->position = $position;

            if (isset($field['translatable'])) {
                $fieldDefinition->isTranslatable = $field['translatable'];
            }
            if (isset($field['required'])) {
                $fieldDefinition->isRequired = $field['required'];
            }
            if (isset($field['searchable'])) {
                $fieldDefinition->isSearchable = $field['searchable'];
            }
            if (isset($field['info_collector'])) {
                $fieldDefinition->isInfoCollector = $field['info_collector'];
            }

            $ct->addFieldDefinition($field);
        }

        return new ContentTypeObject($ct);
    }
}
