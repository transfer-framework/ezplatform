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
            throw new InvalidDataStructureException(
                sprintf('Expected identifier to be of type string, got "%s".', gettype($identifier))
            );
        }
        if (!array_key_exists('fields', $a) || count($a['fields']) == 0) {
            throw new InvalidDataStructureException(
                sprintf('Atleast one field must be defined for identifier "%s".', $identifier)
            );
        }

        $ct = new ContentTypeObject($identifier);

        if (isset($a['main_language_code'])) {
            $ct->mainLanguageCode = $a['main_language_code'];
        }

        if (isset($a['contenttype_groups']) && is_array($a['contenttype_groups'])) {
            $ct->setContentTypeGroups($a['contenttype_groups']);
        } elseif (isset($a['contenttype_group'])) {
            foreach ($a['contenttype_groups'] as $contenttypeGroup) {
                $ct->addContentTypeGroup($contenttypeGroup);
            }
        }

        if (isset($a['names'])) {
            $ct->setNames($a['names']);
        } elseif (isset($a['name'])) {
            $ct->addName($a['name'], $ct->mainLanguageCode);
        }

        if (isset($a['descriptions'])) {
            $ct->setDescriptions($a['descriptions']);
        } elseif (isset($a['description'])) {
            $ct->addDescription($a['description'], $ct->mainLanguageCode);
        }

        if (isset($a['name_schema'])) {
            $ct->nameSchema = $a['name_schema'];
        }

        if (isset($a['url_alias_schema'])) {
            $ct->urlAliasSchema = $a['url_alias_schema'];
        }

        if (isset($a['is_container'])) {
            $ct->isContainer = $a['is_container'];
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
            } elseif (isset($field['description'])) {
                $fieldDefinition->addDescription($field['description'], $ct->mainLanguageCode);
            }
            if (isset($field['field_group'])) {
                $fieldDefinition->fieldGroup = $field['field_group'];
            }
            $position = isset($field['position']) ? $field['position'] : max($positions) + 10;
            $positions[] = $fieldDefinition->position = $position;

            if (isset($field['default_value'])) {
                $fieldDefinition->defaultValue = $field['default_value'];
            }
            if (isset($field['is_translatable'])) {
                $fieldDefinition->isTranslatable = $field['is_translatable'];
            }
            if (isset($field['is_required'])) {
                $fieldDefinition->isRequired = $field['is_required'];
            }
            if (isset($field['is_searchable'])) {
                $fieldDefinition->isSearchable = $field['is_searchable'];
            }
            if (isset($field['is_info_collector'])) {
                $fieldDefinition->isInfoCollector = $field['is_info_collector'];
            }

            $ct->addFieldDefinition($fieldDefinition);
        }

        return $ct;
    }
}
