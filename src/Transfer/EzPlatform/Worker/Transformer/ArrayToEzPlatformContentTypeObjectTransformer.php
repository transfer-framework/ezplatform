<?php

namespace Transfer\EzPlatform\Worker\Transformer;

use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\EzPlatform\Exception\MalformedObjectDataException;
use Transfer\Worker\WorkerInterface;

/**
 * Class ArrayToEzPlatformContentTypeObjectTransformer
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

        $id = key($array);
        $a = $array[$id];

        if(!is_string($id)) {
            throw new \InvalidArgumentException(
                sprintf('Expected identifier to be of type string, got "%s".', gettype($id))
            );
        }
        if(!array_key_exists('fields', $a)) {
            throw new InvalidDataStructureException(
                sprintf('Missing key "%s" in identifier "%s".', 'fields', $id)
            );
        }
        if(count($a['fields']) == 0) {
            throw new InvalidDataStructureException(
                sprintf('Atleast one field must be defined for identifier "%s".', $id)
            );
        }

        $defaultLanguage = isset($a['main_language_code']) ? $a['main_language_code'] : 'eng-GB';

        $ct = array();
        $ct['identifier'] = $id;
        $ct['group_identifier'] = isset($a['main_group_identifier']) ? $a['main_group_identifier'] : 'Content';
        $ct['contenttype_groups'] = isset($a['contenttype_groups']) ? $a['contenttype_groups'] : array('Content');
        $ct['main_language_code'] = $defaultLanguage;
        $ct['names'] = $this->mixedToArray('names', 'name', $a, $defaultLanguage, ucfirst($id));
        $ct['descriptions'] = $this->mixedToArray('descriptions', 'description', $a, $defaultLanguage, '');

        if(!isset($ct['name_schema'])) {
            $ct['name_schema'] = isset($a['name_schema']) ? $a['name_schema'] : '<'.key($a['fields']).'>';
        }
        if(!isset($ct['url_alias_schema'])) {
            $ct['url_alias_schema'] = isset($a['url_alias_schema']) ? $a['url_alias_schema'] : '<'.key($a['fields']).'>';
        }

        $positions = array(0);
        $ct['fields'] = array();
        foreach($a['fields'] as $fieldId => $field) {
            $f = array();
            $f['type'] = isset($field['type']) ? $field['type'] : 'ezstring';

            $f['names'] = $this->mixedToArray('names', 'name', $field, $defaultLanguage, ucfirst($fieldId));
            $f['descriptions'] = $this->mixedToArray('descriptions', 'description', $field, $defaultLanguage, '');

            $f['field_group'] = isset($field['field_group']) ? $field['field_group'] : 'content';
            $f['position'] = isset($field['position']) ? $field['position'] : max($positions) + 10;

            $f['is_translatable'] = isset($field['translatable']) ? $field['translatable'] : false;
            $f['is_required'] = isset($field['required']) ? $field['required'] : false;
            $f['is_searchable'] = isset($field['searchable']) ? $field['searchable'] : false;

            $ct['fields'][$fieldId] = $f;
            $positions[] = $f['position'];
        }

        return new ContentTypeObject($ct);
    }

    protected function mixedToArray($pluralKey, $singularKey, $array, $defaultLanguage, $fallbackValue)
    {
        if(isset($array[$pluralKey])) {
            return $array[$pluralKey];
        }elseif(isset($array[$singularKey])) {
            return array($defaultLanguage => $array[$singularKey]);
        }else{
            return array($defaultLanguage => $fallbackValue);
        }
    }
}