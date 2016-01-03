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
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroup;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeGroupCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinitionUpdateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;
use Transfer\EzPlatform\Data\LanguageObject;
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
     * @var LanguageManager
     */
    private $languageManager;

    /**
     * @param Repository      $repository
     * @param LanguageManager $languageManager
     */
    public function __construct(Repository $repository, LanguageManager $languageManager)
    {
        $this->repository = $repository;
        $this->contentTypeService = $repository->getContentTypeService();
        $this->languageManager = $languageManager;
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
     * @inheritdoc
     */
    public function create(ObjectInterface $object)
    {
        if(!$object instanceof ContentTypeObject) {
            return;
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Creating contenttype %s.', $object->data['identifier']));
        }

        $this->updateContentTypeLanguages($object);

        $contentTypeCreateStruct = $this->contentTypeService->newContentTypeCreateStruct($object->data['identifier']);
        $object->getMapper()->fillContentTypeCreateStruct($contentTypeCreateStruct);

        foreach ($object->fields as $field) {
            $fieldCreateStruct = $this->contentTypeService->newFieldDefinitionCreateStruct($field->data['identifier'], $field->data['type']);
            $field->getMapper()->populateFieldDefinitionCreateStruct($fieldCreateStruct);
            $contentTypeCreateStruct->addFieldDefinition($fieldCreateStruct);
        }

        $contentTypeGroups = $this->loadContentTypeGroupsByIdentifiers($object->data['contenttype_groups']);
        $contentTypeDraft = $this->contentTypeService->createContentType($contentTypeCreateStruct, $contentTypeGroups);

        if ($this->logger) {
            $this->logger->info(sprintf('Created contenttype draft %s.', $object->data['identifier']));
        }
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if ($this->logger) {
            $this->logger->info(sprintf('Published contenttype draft %s.', $object->data['identifier']));
        }

        $this->updateContentTypeGroupsAssignment($object);

        return $this->findByIdentifier($object->data['identifier']);
    }

    /**
     * @inheritdoc
     */
    public function update(ObjectInterface $object)
    {
        if(!$object instanceof ContentTypeObject) {
            return;
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Updating contenttype %s.', $object->data['identifier']));
        }

        $contentType = $this->findByIdentifier($object->data['identifier']);

        if (!$contentType) {
            throw new \Exception(sprintf('Contenttype "%s" not found.', $object->data['identifier']));
        }

        $this->updateContentTypeLanguages($object);

        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id);
        } catch (NotFoundException $e) {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);
        }

        // eZ fields
        $existingFieldDefinitions = $contentType->getFieldDefinitions();

        // Transfer fields
        $updatedFieldDefinitions = $object->fields;

        // Delete field definitions which no longer exist
        $updatedFieldIdentifiers = array();
        foreach ($updatedFieldDefinitions as $updatedFieldDefinition) {
            $updatedFieldIdentifiers[] = $updatedFieldDefinition->data['identifier'];
        }

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

        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();
        $object->getMapper()->fillContentTypeUpdateStruct($contentTypeUpdateStruct);

        $this->contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $this->updateContentTypeGroupsAssignment($object);

        if ($this->logger) {
            $this->logger->info(sprintf('Updated contenttype %s.', $object->data['identifier']));
        }

        // Reload and return contenttype
        return $this->findByIdentifier($object->data['identifier']);
    }

    /**
     * @inheritdoc
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if(!$object instanceof ContentTypeObject) {
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
     * @inheritdoc
     */
    public function remove(ObjectInterface $object)
    {
        if(!$object instanceof ContentTypeObject) {
            return;
        }

        return $this->removeByIdentifier($object->data['identifier']);
    }

    /**
     * @param string $identifier
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
        $definition = $this->contentTypeService->newFieldDefinitionCreateStruct($field->data['identifier'], $field->data['type']);
        $field->getMapper()->populateFieldDefinitionCreateStruct($definition);

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
        $field->getMapper()->populateFieldDefinitionUpdateStruct($definition);

        return $definition;
    }

    /**
     * @param ContentTypeObject $object
     */
    private function updateContentTypeLanguages(ContentTypeObject $object)
    {
        $languageCodes = $object->getLanguageCodes();
        foreach ($languageCodes as $languageCode) {
            $this->languageManager->create(new LanguageObject(array('code' => $languageCode)));
        }
    }

    /*
     * @param array $identifiers
     *
     * @return ContentTypeGroup[]
     */
    protected function loadContentTypeGroupsByIdentifiers(array $identifiers)
    {
        $contentTypeGroups = array_map(
            function ($identifier) {
                try {
                    return $this->contentTypeService->loadContentTypeGroupByIdentifier($identifier);
                } catch (NotFoundException $notFoundException) {
                    return $this->createContentTypeGroupByIdentifier($identifier);
                }
            },
            $identifiers
        );

        return $contentTypeGroups;
    }

    /**
     * @param string $contentTypeGroupIdentifier
     *
     * @return ContentTypeGroup
     */
    protected function createContentTypeGroupByIdentifier($contentTypeGroupIdentifier)
    {
        $contentTypeGroupCreateStruct = new ContentTypeGroupCreateStruct();
        $contentTypeGroupCreateStruct->identifier = $contentTypeGroupIdentifier;

        return $this->contentTypeService->createContentTypeGroup($contentTypeGroupCreateStruct);
    }

    /**
     * @param ContentTypeObject $object
     *
     * @return bool
     *
     * @throws NotFoundException
     * @throws \Exception
     */
    protected function updateContentTypeGroupsAssignment(ContentTypeObject $object)
    {
        // Load contenttype
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($object->data['identifier']);

        // Get identifiers of current contenttypegroups
        $currentContentTypeGroupIdentifiers = array_map(
            function (ContentTypeGroup $contentTypeGroup) {
                return $contentTypeGroup->identifier;
            },
            $contentType->getContentTypeGroups()
        );

        // Get new contenttypegroup identifiers
        $newContentTypeGroupIdentifiers = $object->data['contenttype_groups'];

        // Compare identifiers to identify which once to add/remove/keep
        $remove = array_diff($currentContentTypeGroupIdentifiers, $newContentTypeGroupIdentifiers);
        $add = array_diff($newContentTypeGroupIdentifiers, $currentContentTypeGroupIdentifiers);

        $this->attachContentTypeGroupsByIdentifiers($contentType, $add);
        $this->detachContentTypeGroupsByIdentifiers($contentType, $remove);

        return true;
    }

    /**
     * Load (and create if not exists) new contenttype groups, and assign them to a contenttype.
     *
     * @param ContentType $contentType
     * @param array       $contentTypeGroupsIdentifiers
     *
     * @throws NotFoundException
     */
    protected function attachContentTypeGroupsByIdentifiers(ContentType $contentType, array $contentTypeGroupsIdentifiers)
    {
        $contentTypeGroups = $this->loadContentTypeGroupsByIdentifiers($contentTypeGroupsIdentifiers);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->contentTypeService->assignContentTypeGroup($contentType, $contentTypeGroup);
        }
    }

    /**
     * Load contenttype groups, and unassign them from a contenttype.
     *
     * @param ContentType $contentType
     * @param array       $contentTypeGroupsIdentifiers
     *
     * @throws NotFoundException
     */
    protected function detachContentTypeGroupsByIdentifiers(ContentType $contentType, array $contentTypeGroupsIdentifiers)
    {
        $contentTypeGroups = $this->loadContentTypeGroupsByIdentifiers($contentTypeGroupsIdentifiers);
        foreach ($contentTypeGroups as $contentTypeGroup) {
            $this->contentTypeService->unassignContentTypeGroup($contentType, $contentTypeGroup);
        }
    }

}
