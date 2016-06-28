<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Sub;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Repository\Values\FieldDefinitionObject;

/**
 * Content type manager.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class FieldDefinitionSubManager implements LoggerAwareInterface
{
    /**
     * @var ContentTypeService Content type service
     */
    private $contentTypeService;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * FieldDefinitionSubManager constructor.
     *
     * @param ContentTypeService $contentTypeService
     */
    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ContentTypeCreateStruct $createStruct
     * @param FieldDefinitionObject[] $fields
     */
    public function addFieldsToCreateStruct(ContentTypeCreateStruct $createStruct, array $fields)
    {
        foreach ($fields as $field) {
            /* @var FieldDefinitionObject $field */
            $fieldCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct($field->data['identifier'], $field->data['type']);
            $field->getMapper()->mapObjectToCreateStruct($fieldCreateStruct);
            $createStruct->addFieldDefinition($fieldCreateStruct);
        }
    }

    /**
     * Creating new and updates existing field definitions.
     * NOTE: Will NOT delete field definitions which no longer exist.
     *
     * @param FieldDefinitionObject[] $updatedFieldDefinitions
     * @param FieldDefinition[]       $existingFieldDefinitions
     * @param ContentTypeDraft        $contentTypeDraft
     */
    public function createOrUpdateFieldDefinitions(array $updatedFieldDefinitions, array $existingFieldDefinitions, ContentTypeDraft $contentTypeDraft)
    {
        foreach ($updatedFieldDefinitions as $updatedField) {

            // Updating existing field definitions
            foreach ($existingFieldDefinitions as $existingField) {
                if ($existingField->identifier == $updatedField->data['identifier']) {
                    $this->contentTypeService->updateFieldDefinition(
                        $contentTypeDraft,
                        $existingField,
                        $this->updateFieldDefinition($updatedField)
                    );
                    continue 2;
                }
            }

            // Creating new field definitions
            $this->contentTypeService->addFieldDefinition(
                $contentTypeDraft,
                $this->createFieldDefinition($updatedField)
            );
        }
    }

    /**
     * @param FieldDefinitionObject $field
     *
     * @return FieldDefinitionCreateStruct
     */
    private function createFieldDefinition(FieldDefinitionObject $field)
    {
        $definition = $this->contentTypeService->newFieldDefinitionCreateStruct($field->data['identifier'], $field->data['type']);
        $field->getMapper()->mapObjectToCreateStruct($definition);

        return $definition;
    }

    /**
     * @param FieldDefinitionObject $field
     *
     * @return FieldDefinitionUpdateStruct
     */
    private function updateFieldDefinition(FieldDefinitionObject $field)
    {
        $definition = $this->contentTypeService->newFieldDefinitionUpdateStruct();
        $field->getMapper()->mapObjectToUpdateStruct($definition);

        return $definition;
    }
}
