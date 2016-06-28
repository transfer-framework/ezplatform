<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Transfer\EzPlatform\Repository\Values\FieldDefinitionObject;

/**
 * Field definition mapper.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class FieldDefinitionMapper
{
    /**
     * @var FieldDefinitionObject
     */
    public $fieldDefinitionObject;

    /**
     * @param FieldDefinitionObject $fieldDefinitionObject
     */
    public function __construct(FieldDefinitionObject $fieldDefinitionObject)
    {
        $this->fieldDefinitionObject = $fieldDefinitionObject;
    }

    /**
     * @param FieldDefinitionCreateStruct $createStruct
     */
    public function mapObjectToCreateStruct(FieldDefinitionCreateStruct $createStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'names' => 'names',
            'descriptions' => 'descriptions',
            'fieldGroup' => 'field_group',
            'position' => 'position',
            'isTranslatable' => 'is_translatable',
            'isRequired' => 'is_required',
            'isInfoCollector' => 'is_info_collector',
            'isSearchable' => 'is_searchable',
            'fieldSettings' => 'field_settings',
            'defaultValue' => 'default_value',
            'identifier' => 'identifier',
            'validatorConfiguration' => 'validator_configuration',
            'fieldTypeIdentifier' => 'type',
        );

        $this->arrayToStruct($createStruct, $keys);

        $this->callStruct($createStruct);
    }

    /**
     * @param FieldDefinitionUpdateStruct $updateStruct
     */
    public function mapObjectToUpdateStruct(FieldDefinitionUpdateStruct $updateStruct)
    {
        // Name collection (ez => transfer)
        $keys = array(
            'names' => 'names',
            'descriptions' => 'descriptions',
            'fieldGroup' => 'field_group',
            'position' => 'position',
            'isTranslatable' => 'is_translatable',
            'isRequired' => 'is_required',
            'isInfoCollector' => 'is_info_collector',
            'isSearchable' => 'is_searchable',
            'fieldSettings' => 'field_settings',
            'defaultValue' => 'default_value',
            'identifier' => 'identifier',
            'validatorConfiguration' => 'validator_configuration',
        );

        $this->arrayToStruct($updateStruct, $keys);

        $this->callStruct($updateStruct);
    }

    /**
     * @param FieldDefinitionCreateStruct|FieldDefinitionUpdateStruct $struct
     * @param array                                                   $keys
     */
    private function arrayToStruct($struct, $keys)
    {
        foreach ($keys as $ezKey => $transferKey) {
            if (isset($this->fieldDefinitionObject->data[$transferKey])) {
                $struct->$ezKey = $this->fieldDefinitionObject->data[$transferKey];
            }
        }
    }

    /**
     * @param FieldDefinitionCreateStruct|FieldDefinitionUpdateStruct $struct
     */
    private function callStruct($struct)
    {
        if ($this->fieldDefinitionObject->getProperty('struct_callback')) {
            $callback = $this->fieldDefinitionObject->getProperty('struct_callback');
            $callback($struct);
        }
    }
}
