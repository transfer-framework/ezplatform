<?php

/**
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
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeDraft;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Repository\Manager\Sub\ContentTypeGroupSubManager;
use Transfer\EzPlatform\Repository\Manager\Sub\FieldDefinitionSubManager;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\Repository\Values\LanguageObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\FinderInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content type manager.
 *
 * @internal
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class ContentTypeManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface, FinderInterface
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
     * @var FieldDefinitionSubManager
     */
    private $fieldDefinitionSubManager;

    /**
     * @var ContentTypeGroupSubManager
     */
    private $contentTypeGroupSubManager;

    /**
     * @param Repository      $repository
     * @param LanguageManager $languageManager
     */
    public function __construct(Repository $repository, LanguageManager $languageManager)
    {
        $this->repository = $repository;
        $this->contentTypeService = $repository->getContentTypeService();
        $this->languageManager = $languageManager;
        $this->fieldDefinitionSubManager = new FieldDefinitionSubManager($repository->getContentTypeService());
        $this->contentTypeGroupSubManager = new ContentTypeGroupSubManager($repository->getContentTypeService());
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function find(ValueObject $object)
    {
        try {
            if (isset($object->data['identifier'])) {
                $contentType = $this->contentTypeService->loadContentTypeByIdentifier($object->data['identifier']);
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($contentType)) {
            throw new ObjectNotFoundException(ContentType::class, array('identifier'));
        }

        return $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            throw new UnsupportedObjectOperationException(ContentTypeObject::class, get_class($object));
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Creating contenttype %s.', $object->data['identifier']));
        }

        $this->updateContentTypeLanguages($object);

        $contentTypeCreateStruct = $this->contentTypeService->newContentTypeCreateStruct($object->data['identifier']);
        $object->getMapper()->mapObjectToCreateStruct($contentTypeCreateStruct);

        $this->fieldDefinitionSubManager->addFieldsToCreateStruct($contentTypeCreateStruct, $object->data['fields']);

        $contentTypeGroups = $this->contentTypeGroupSubManager->loadContentTypeGroupsByIdentifiers($object->data['contenttype_groups']);
        $contentTypeDraft = $this->contentTypeService->createContentType($contentTypeCreateStruct, $contentTypeGroups);

        if ($this->logger) {
            $this->logger->info(sprintf('Created contenttype draft %s.', $object->data['identifier']));
        }
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);
        if ($this->logger) {
            $this->logger->info(sprintf('Published contenttype draft %s.', $object->data['identifier']));
        }

        $this->contentTypeGroupSubManager->updateContentTypeGroupsAssignment($object);

        $object->getMapper()->contentTypeToObject(
            $this->find($object)
        );

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            throw new UnsupportedObjectOperationException(ContentTypeObject::class, get_class($object));
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Updating contenttype %s.', $object->data['identifier']));
        }

        $contentType = $this->find($object);

        $this->updateContentTypeLanguages($object);

        $contentTypeDraft = $this->getNewContentTypeDraft($contentType);

        // Creating or updating the fielddefinitions
        $this->fieldDefinitionSubManager->createOrUpdateFieldDefinitions(
            $object->data['fields'],
            $contentType->getFieldDefinitions(),
            $contentTypeDraft
        );

        $contentTypeUpdateStruct = $this->contentTypeService->newContentTypeUpdateStruct();
        $object->getMapper()->mapObjectToUpdateStruct($contentTypeUpdateStruct);

        $this->contentTypeService->updateContentTypeDraft($contentTypeDraft, $contentTypeUpdateStruct);
        $this->contentTypeService->publishContentTypeDraft($contentTypeDraft);

        $this->contentTypeGroupSubManager->updateContentTypeGroupsAssignment($object);

        if ($this->logger) {
            $this->logger->info(sprintf('Updated contenttype %s.', $object->data['identifier']));
        }

        $object->getMapper()->contentTypeToObject(
            $this->find($object)
        );

        return $object;
    }

    /**
     * @param ContentType $contentType
     *
     * @return ContentTypeDraft
     */
    private function getNewContentTypeDraft(ContentType $contentType)
    {
        try {
            $contentTypeDraft = $this->contentTypeService->loadContentTypeDraft($contentType->id);
        } catch (NotFoundException $e) {
            $contentTypeDraft = $this->contentTypeService->createContentTypeDraft($contentType);
        }

        return $contentTypeDraft;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            throw new UnsupportedObjectOperationException(ContentTypeObject::class, get_class($object));
        }

        try {
            $this->find($object);

            return $this->update($object);
        } catch (NotFoundException $notFound) {
            return $this->create($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof ContentTypeObject) {
            throw new UnsupportedObjectOperationException(ContentTypeObject::class, get_class($object));
        }

        try {
            $contentType = $this->find($object);
            $this->contentTypeService->deleteContentType($contentType);

            return true;
        } catch (NotFoundException $notFound) {
            return false;
        }
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
}
