<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Content;

use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use eZ\Publish\API\Repository\Values\ValueObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;

/**
 * Field definition mapper.
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
    public function populateCreateStruct(FieldDefinitionCreateStruct $fieldDefinitionStruct)
    {
        $this->populateStruct($fieldDefinitionStruct);
    }

    /**
     * @param FieldDefinitionUpdateStruct $fieldDefinitionStruct
     */
    public function populateUpdateStruct(FieldDefinitionUpdateStruct $fieldDefinitionStruct)
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
        $fieldDefinitionStruct->names = $this->fieldDefinitionObject->getNames();
        $fieldDefinitionStruct->descriptions = $this->fieldDefinitionObject->getDescriptions();
        $fieldDefinitionStruct->fieldGroup = $this->fieldDefinitionObject->fieldGroup;
        $fieldDefinitionStruct->position = $this->fieldDefinitionObject->position;
        $fieldDefinitionStruct->isTranslatable = $this->fieldDefinitionObject->isTranslatable;
        $fieldDefinitionStruct->isRequired = $this->fieldDefinitionObject->isRequired;
        $fieldDefinitionStruct->isInfoCollector = $this->fieldDefinitionObject->isInfoCollector;
        $fieldDefinitionStruct->isSearchable = $this->fieldDefinitionObject->isSearchable;

        return $fieldDefinitionStruct;
    }
}
