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
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;

/**
 * Content type manager.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeManager implements LoggerAwareInterface
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
     * @return ContentType|false
     */
    public function findByIdentifier($identifier)
    {
        if (!is_string($identifier)) {
            return false;
        }

        try {
            $contentType = $this->contentTypeService->loadContentTypeByIdentifier($identifier);
        } catch (NotFoundException $e) {
            return false;
        }

        return $contentType;
    }

    /**
     * Create a ContentType object and returns it.
     *  Will return null if the argument is not of an ContentTypeObject.
     *  Will return false if we were unable to fetch the ContentType after creating it.
     *
     * @param ContentTypeObject $object
     *
     * @return ContentType|false|null
     */
    public function create(ContentTypeObject $object)
    {
        if ($this->logger) {
            $this->logger->info(sprintf('Creating contenttype %s.', $object->getIdentifier()));
        }

        $contentTypeCreateStruct = $this->contentTypeService->newContentTypeCreateStruct($object->getIdentifier());
        $contentTypeCreateStruct->names = $object->getNames();
        $contentTypeCreateStruct->remoteId = sha1(microtime());
        $contentTypeCreateStruct->isContainer = $object->isContainer;
        $contentTypeCreateStruct->mainLanguageCode = $object->mainLanguageCode;
        $contentTypeCreateStruct->nameSchema = $object->nameSchema;
        $contentTypeCreateStruct->urlAliasSchema = $object->urlAliasSchema;
        $contentTypeCreateStruct->descriptions = $object->getDescriptions();
        $contentTypeCreateStruct->isContainer = $object->isContainer;
        $contentTypeCreateStruct->defaultAlwaysAvailable = $object->defaultAlwaysAvailable;
        $contentTypeCreateStruct->defaultSortField = $object->defaultSortField;
        $contentTypeCreateStruct->defaultSortOrder = $object->defaultSortOrder;

        foreach ($object->getFieldDefinitions() as $field) {
            /* @var FieldDefinitionObject $field */
            $titleFieldCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct($field->getIdentifier(), $field->type);
            $titleFieldCreateStruct->names = $field->getNames();
            $titleFieldCreateStruct->descriptions = $field->getDescriptions();
            $titleFieldCreateStruct->fieldGroup = $field->fieldGroup;
            $titleFieldCreateStruct->position = $field->position;
            $titleFieldCreateStruct->isTranslatable = $field->isTranslatable;
            $titleFieldCreateStruct->isRequired = $field->isRequired;
            $titleFieldCreateStruct->isSearchable = $field->isSearchable;
            $titleFieldCreateStruct->isInfoCollector = $field->isInfoCollector;
            $contentTypeCreateStruct->addFieldDefinition($titleFieldCreateStruct);
        }

        $contentTypeGroup = $this->contentTypeService->loadContentTypeGroupByIdentifier($object->getMainGroupIdentifier());
        $contentTypeDraft = $this->contentTypeService->createContentType($contentTypeCreateStruct, array($contentTypeGroup));

        if ($this->logger) {
            $this->logger->info(sprintf('Created contenttype draft %s.', $object->getIdentifier()));
        }
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if ($this->logger) {
            $this->logger->info(sprintf('Published contenttype draft %s.', $object->getIdentifier()));
        }

        return $this->findByIdentifier($object->getIdentifier());
    }

    /**
     * Updates a ContentType and FieldTypes and returns it.
     *  Will return null if the argument is not of an ContentTypeObject.
     *  Will return false if we were unable to fetch the ContentType after updating it.
     *
     * @param ContentTypeObject $object
     *
     * @return ContentType|false|null
     */
    public function update(ContentTypeObject $object)
    {
        if ($this->logger) {
            $this->logger->info(sprintf('Updating contenttype %s.', $object->getIdentifier()));
        }

        $contentType = $this->findByIdentifier($object->getIdentifier());

        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id);
        } catch (NotFoundException $e) {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);
        }

        // eZ fields
        $existingFieldDefinitions = $contentType->getFieldDefinitions();

        // Transfer fields
        $updatedFieldDefinitions = $object->getFieldDefinitions();

        // Delete field definitions which no longer exist
        $updatedFieldIdentifiers = array();
        foreach ($updatedFieldDefinitions as $updatedFieldDefinition) {
            $updatedFieldIdentifiers[] = $updatedFieldDefinition->getIdentifier();
        }

        foreach ($updatedFieldDefinitions as $updatedField) {

            // Updating existing field definitions
            foreach ($existingFieldDefinitions as $existingField) {
                if ($existingField->identifier == $updatedField->getIdentifier()) {
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

        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();
        $object->fillContentTypeStruct($contentTypeUpdateStruct);
        $this->contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if ($this->logger) {
            $this->logger->info(sprintf('Updated contenttype %s.', $object->getIdentifier()));
        }

        return $this->findByIdentifier($object->getIdentifier());
    }

    /**
     * @see ContentTypeManager::create
     * @see ContentTypeManager::update
     *
     * @param ContentTypeObject $object
     *
     * @return ContentType|false|null
     */
    public function createOrUpdate(ContentTypeObject $object)
    {
        $contentObject = $this->findByIdentifier($object->getIdentifier());
        if (!$contentObject) {
            return $this->create($object);
        } else {
            return $this->update($object);
        }
    }

    /**
     * @param string$identifier
     *
     * @return bool
     */
    public function removeByIdentifier($identifier)
    {
        $contentType = $this->findByIdentifier($identifier);

        if (!$contentType) {
            return true;
        }

        $this->contentTypeService->deleteContentType($contentType);

        return true;
    }

    /**
     * @param FieldDefinitionObject $field
     *
     * @return FieldDefinitionCreateStruct
     */
    private function createFieldDefinition(FieldDefinitionObject $field)
    {
        $definition = $this->contentTypeService->newFieldDefinitionCreateStruct($field->getIdentifier(), $field->type);
        $field->fillFieldDefinitionUpdateStruct($definition);

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
        $field->fillFieldDefinitionUpdateStruct($definition);

        return $definition;
    }
}
