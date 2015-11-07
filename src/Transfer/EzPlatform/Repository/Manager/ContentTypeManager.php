<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content type manager.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface Logger
     */
    private $logger;

    /**
     * @var ContentTypeService Content type service
     */
    private $contentTypeService;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->contentTypeService = $repository->getContentTypeService();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Finds content type object by identifier.
     *
     * @param string $identifier Identifier
     *
     * @return ContentType
     */
    public function findByIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            return;
        }

        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($identifier);
        } catch (NotFoundException $e) {
            return false;
        }

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            if($this->logger) {
                $this->logger->warning(sprintf('%s excepted object of type ContentTypeObject, got "%s". Skipping.', __METHOD__,  get_class($object)));
            }
            return;
        }

        if($this->logger) {
            $this->logger->info(sprintf('Creating contenttype %s.', $object->data['identifier']));
        }

        $contentTypeCreateStruct = $this->contentTypeService->newContentTypeCreateStruct($object->data['identifier']);
        $contentTypeCreateStruct->mainLanguageCode = $object->data['main_language_code'];
        $contentTypeCreateStruct->nameSchema = $object->data['name_schema'];
        $contentTypeCreateStruct->urlAliasSchema = $object->data['url_alias_schema'];
        $contentTypeCreateStruct->names = $object->data['names'];
        $contentTypeCreateStruct->descriptions = $object->data['descriptions'];

        foreach ($object->data['fields'] as $identifier => $field) {
            $titleFieldCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct($identifier, $field['type']);
            $titleFieldCreateStruct->names = $field['names'];
            $titleFieldCreateStruct->descriptions = $field['descriptions'];
            $titleFieldCreateStruct->fieldGroup = $field['field_group'];
            $titleFieldCreateStruct->position = $field['position'];
            $titleFieldCreateStruct->isTranslatable = $field['is_translatable'];
            $titleFieldCreateStruct->isRequired = $field['is_required'];
            $titleFieldCreateStruct->isSearchable = $field['is_searchable'];
            $contentTypeCreateStruct->addFieldDefinition($titleFieldCreateStruct);
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier($object->data['group_identifier']);
        $contentTypeDraft = $this->contentTypeService->createContentType($contentTypeCreateStruct, array($contentTypeGroup));
        if($this->logger) {
            $this->logger->info(sprintf('Created contenttype %s.', $object->data['identifier']));
        }
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if($this->logger) {
            $this->logger->info(sprintf('Published contenttype %s.', $object->data['identifier']));
        }

        return $this->findByIdentifier($object->data['identifier']);
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            if($this->logger) {
                $this->logger->warning(sprintf('%s excepted object of type ContentTypeObject, got "%s". Skipping.', __METHOD__,  get_class($object)));
            }
            return;
        }

        if($this->logger) {
            $this->logger->info(sprintf('Updating contenttype %s.', $object->data['identifier']));
        }

        $contentType = $this->findByIdentifier($object->data['identifier']);

        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id);
        } catch (NotFoundException $e) {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);
        }

        $existingFieldDefinitions = $contentType->getFieldDefinitions();
        $updatedFieldDefinitions = $object->data['fields'];

        // Delete field definitions which no longer exist
        foreach (array_filter($existingFieldDefinitions, function($existingFieldDefinition) use ($updatedFieldDefinitions) {
            return !array_key_exists($existingFieldDefinition->identifier, $updatedFieldDefinitions);
        }) as $deleteFieldDefinition) {
            $this->contentTypeService->removeFieldDefinition($contentTypeDraft, $deleteFieldDefinition);
        };

        foreach ($updatedFieldDefinitions as $identifier => $updatedField) {

            // Check if the field definition should be updated
            foreach ($existingFieldDefinitions as $existingField) {
                if ($existingField->identifier == $identifier) {
                    $fieldDefinitionUpdateStruct = $this->contentTypeService->newFieldDefinitionUpdateStruct();
                    $fieldDefinitionUpdateStruct->names = $updatedField['names'];
                    $fieldDefinitionUpdateStruct->descriptions = $updatedField['descriptions'];
                    $fieldDefinitionUpdateStruct->fieldGroup = $updatedField['field_group'];
                    $fieldDefinitionUpdateStruct->position = $updatedField['position'];
                    $fieldDefinitionUpdateStruct->isTranslatable = $updatedField['is_translatable'];
                    $fieldDefinitionUpdateStruct->isRequired = $updatedField['is_required'];
                    $fieldDefinitionUpdateStruct->isSearchable = $updatedField['is_searchable'];
                    $this->contentTypeService->updateFieldDefinition($contentTypeDraft, $existingField, $fieldDefinitionUpdateStruct);
                    continue 2;
                }
            }

            // Otherwise, create a new field definition
            $fieldDefinitionCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct($identifier, $updatedField['type']);
            $fieldDefinitionCreateStruct->names = $updatedField['names'];
            $fieldDefinitionCreateStruct->descriptions = $updatedField['descriptions'];
            $fieldDefinitionCreateStruct->fieldGroup = $updatedField['field_group'];
            $fieldDefinitionCreateStruct->position = $updatedField['position'];
            $fieldDefinitionCreateStruct->isTranslatable = $updatedField['is_translatable'];
            $fieldDefinitionCreateStruct->isRequired = $updatedField['is_required'];
            $fieldDefinitionCreateStruct->isSearchable = $updatedField['is_searchable'];
            $this->contentTypeService->addFieldDefinition($contentTypeDraft, $fieldDefinitionCreateStruct);
        }

        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();
        $contentTypeUpdateStruct->mainLanguageCode = $object->data['main_language_code'];
        $contentTypeUpdateStruct->nameSchema = $object->data['name_schema'];
        $contentTypeUpdateStruct->urlAliasSchema = $object->data['url_alias_schema'];
        $contentTypeUpdateStruct->names = $object->data['names'];
        $contentTypeUpdateStruct->descriptions = $object->data['descriptions'];
        $this->contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
        if($this->logger) {
            $this->logger->info(sprintf('Updated contenttype %s.', $object->data['identifier']));
        }

        return $this->findByIdentifier($object->data['identifier']);
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            if($this->logger) {
                $this->logger->warning(sprintf('%s excepted object of type ContentTypeObject, got "%s". Skipping.', __METHOD__,  get_class($object)));
            }
            return;
        }

        $contentObject = $this->findByIdentifier($object->data['identifier']);
        if (!$contentObject) {
            return $this->create($object);
        } else {
            return $this->update($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof ValueObject) {
            return;
        }

        $contentType = $this->findByIdentifier($object->data['identifier']);

        if (!$contentType) {
            return true;
        }

        $this->contentTypeService->deleteContentType($contentType);

        return true;
    }
}
