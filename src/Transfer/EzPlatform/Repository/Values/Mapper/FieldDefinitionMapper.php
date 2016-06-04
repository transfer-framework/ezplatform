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
use eZ\Publish\API\Repository\Values\ValueObject;
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
     * @param FieldDefinitionCreateStruct $fieldDefinitionStruct
     */
    public function populateFieldDefinitionCreateStruct(FieldDefinitionCreateStruct $fieldDefinitionStruct)
    {
        $this->populateStruct($fieldDefinitionStruct);
    }

    /**
     * @param FieldDefinitionUpdateStruct $fieldDefinitionStruct
     */
    public function populateFieldDefinitionUpdateStruct(FieldDefinitionUpdateStruct $fieldDefinitionStruct)
    {
        $this->populateStruct($fieldDefinitionStruct);
    }

    /**
     * @param ValueObject $fieldDefinitionStruct
     *
     * @return ValueObject
     */
    protected function populateStruct(ValueObject $fieldDefinitionStruct)
    {
        $fieldDefinitionStruct->names = $this->fieldDefinitionObject->data['names'];
        $fieldDefinitionStruct->descriptions = $this->fieldDefinitionObject->data['descriptions'];
        $fieldDefinitionStruct->fieldGroup = $this->fieldDefinitionObject->data['field_group'];
        $fieldDefinitionStruct->position = $this->fieldDefinitionObject->data['position'];
        $fieldDefinitionStruct->isTranslatable = $this->fieldDefinitionObject->data['is_translatable'];
        $fieldDefinitionStruct->isRequired = $this->fieldDefinitionObject->data['is_required'];
        $fieldDefinitionStruct->isInfoCollector = $this->fieldDefinitionObject->data['is_info_collector'];
        $fieldDefinitionStruct->isSearchable = $this->fieldDefinitionObject->data['is_searchable'];

        return $fieldDefinitionStruct;
    }
}
