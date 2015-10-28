<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Exception\MalformedObjectDataException;
use Transfer\EzPlatform\Exception\MissingIdentificationPropertyException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content manager.
 */
class ContentManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var ContentService
     */
    protected $contentService;

    /**
     * @var ContentTypeService
     */
    protected $contentTypeService;

    /**
     * @var LocationService
     */
    protected $locationService;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var array List of created content objects.
     */
    private $created = array();

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->contentService = $repository->getContentService();
        $this->contentTypeService = $repository->getContentTypeService();
        $this->locationService = $repository->getLocationService();
        $this->repository = $repository;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Finds a content object by remote ID.
     *
     * @param string $remoteId Remote ID
     *
     * @return ContentObject
     */
    public function findByRemoteId($remoteId)
    {
        try {
            $content = $this->contentService->loadContentByRemoteId($remoteId);
        }
        catch (\Exception $e) {
            return null;
        }

        $object = new ContentObject($content->fields);
        $object->setContentInfo($content->contentInfo);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            return;
        }

        $createStruct = $this->contentService->newContentCreateStruct(
            $this->contentTypeService->loadContentTypeByIdentifier($object->getProperty('content_type_identifier')),
            $object->getProperty('language')
        );

        $this->mapObjectToStruct($object, $createStruct);

        $content = $this->contentService->createContent($createStruct);
        $this->contentService->publishVersion($content->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::create'));
        }

        $object->setVersionInfo($content->versionInfo);
        $object->setContentInfo($content->contentInfo);

        $this->created[] = $object;

        // @TODO Return ContentObject
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            return;
        }

        $contentDraft = $this->contentService->createContentDraft($object->getProperty('content_info'));

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $this->mapObjectToUpdateStruct($object, $contentUpdateStruct);

        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $content = $this->contentService->publishVersion($contentDraft->versionInfo);

        $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::update'));

        $object->setVersionInfo($content->versionInfo);
        $object->setContentInfo($content->contentInfo);

        // @TODO Return ContentObject
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            return;
        }

        if (!$object->getProperty('content_id') && !$object->getProperty('remote_id')) {
            throw new MissingIdentificationPropertyException($object);
        }

        if ($existingObject = $this->findByRemoteId($object->getRemoteId())) {
            if ($object->getProperty('update') === false) {
                return;
            }

            $this->logger->info(
                sprintf('Found existing content object with ID %d for %s',
                    $existingObject->getProperty('content_info')->id,
                    $existingObject->getProperty('name')
                ),
                array('ContentManager::createOrUpdate')
            );

            return $this->update($existingObject);
        } else {
            if ($this->logger) {
                $this->logger->info(
                    sprintf(
                        'No existing content object for %s. Creating new content object...',
                        $object->getProperty('name')
                    ),
                    array('ContentManager::createOrUpdate')
                );
            }

            return $this->create($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            return false;
        }

        $object = $this->findByRemoteId($object->getRemoteId());

        if ($object->getProperty('content_info')) {
            $this->contentService->deleteContent($object->getProperty('content_info'));

            return true;
        }

        return false;
    }

    /**
     * Assigns a main location ID for a content object.
     *
     * @param ContentObject $object   Content object
     * @param Location      $location Location
     *
     * @return Content
     */
    public function setMainLocation(ContentObject $object, Location $location)
    {
        $contentMetadataUpdateStruct = $this->contentService->newContentMetadataUpdateStruct();

        $contentMetadataUpdateStruct->mainLocationId = $location->id;

        $object->setMainLocationId($location->id);

        if ($object->getContentInfo() == null) {
            return null;
        }

        return $this->contentService->updateContentMetadata($object->getContentInfo(), $contentMetadataUpdateStruct);
    }

    /**
     * Tests if a content object is new.
     *
     * @param ContentObject $object Content object to test
     *
     * @return bool True, if new
     */
    public function isNew(ContentObject $object)
    {
        /** @var ContentObject $createdObject */
        foreach ($this->created as $createdObject) {
            if ($createdObject == $object || $createdObject->getRemoteId() == $object->getRemoteId()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Maps object data to create struct.
     *
     * @param ContentObject       $object       Content object to map from
     * @param ContentCreateStruct $createStruct Content create struct to map to
     *
     * @throws MalformedObjectDataException
     */
    private function mapObjectToStruct(ContentObject $object, ContentCreateStruct $createStruct)
    {
        if (!is_array($object->data)) {
            throw new MalformedObjectDataException();
        }

        $this->assignStructFieldValues($object, $createStruct);

        if ($object->getProperty('language')) {
            $createStruct->mainLanguageCode = $object->getProperty('language');
        }

        if ($object->getProperty('remote_id')) {
            $createStruct->remoteId = $object->getProperty('remote_id');
        }

        if ($object->getProperty('modification_date')) {
            $createStruct->modificationDate = $object->getProperty('modification_date');
        }
    }

    /**
     * Maps object data to update struct.
     *
     * @param ContentObject       $object              Content object to map from
     * @param ContentUpdateStruct $contentUpdateStruct Content update struct to map to
     *
     * @throws MalformedObjectDataException
     */
    private function mapObjectToUpdateStruct(ContentObject $object, ContentUpdateStruct $contentUpdateStruct)
    {
        if (!is_array($object->data)) {
            throw new MalformedObjectDataException();
        }

        $this->assignStructFieldValues($object, $contentUpdateStruct);

        if ($object->getProperty('language')) {
            $contentUpdateStruct->initialLanguageCode = $object->getProperty('language');
        }
    }

    /**
     * Copies content object data from a struct.
     *
     * @param ContentObject $object Content object to get values from
     * @param object        $struct Struct to assign values to
     */
    private function assignStructFieldValues(ContentObject $object, $struct)
    {
        foreach ($object->data as $key => $value) {
            $struct->setField($key, $value);
        }
    }
}
