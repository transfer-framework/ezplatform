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
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\ContentUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\LocationObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\FinderInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Content manager.
 *
 * @internal
 */
class ContentManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface, FinderInterface
{
    /**
     * @var LocationManager
     */
    private $locationManager;

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
     * @param Repository      $repository
     * @param LocationManager $locationManager
     */
    public function __construct(Repository $repository, LocationManager $locationManager)
    {
        $this->locationManager = $locationManager;
        $this->contentService = $repository->getContentService();
        $this->contentTypeService = $repository->getContentTypeService();
        $this->locationService = $repository->getLocationService();
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
            if ($object->getProperty('remote_id')) {
                $content = $this->contentService->loadContentByRemoteId($object->getProperty('remote_id'));
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($content)) {
            throw new ObjectNotFoundException(Content::class, array('remote_id'));
        }

        return $content;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new UnsupportedObjectOperationException(ContentObject::class, get_class($object));
        }

        $createStruct = $this->contentService->newContentCreateStruct(
            $this->contentTypeService->loadContentTypeByIdentifier($object->getProperty('content_type_identifier')),
            $object->getProperty('language')
        );

        $this->mapObjectToContentStruct($object, $createStruct);

        /** @var LocationObject[] $locationObjects */
        $locationObjects = $object->getProperty('parent_locations');
        $locationCreateStructs = [];
        if (is_array($locationObjects) && count($locationObjects) > 0) {
            foreach ($locationObjects as $locationObject) {
                $locationCreateStruct = $this->locationService->newLocationCreateStruct($locationObject->data['parent_location_id']);
                $locationObject->getMapper()->getNewLocationCreateStruct($locationCreateStruct);
                $locationCreateStructs[] = $locationCreateStruct;
            }
        }

        $content = $this->contentService->createContent($createStruct, $locationCreateStructs);
        $this->contentService->publishVersion($content->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::create'));
        }

        $object->setProperty('id', $content->contentInfo->id);
        $object->setProperty('version_info', $content->versionInfo);
        $object->setProperty('content_info', $content->contentInfo);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new UnsupportedObjectOperationException(ContentObject::class, get_class($object));
        }

        $existingContent = $this->find($object);
        if (null === $object->getProperty('content_info')) {
            $object->setProperty('content_info', $existingContent->contentInfo);
        }

        $contentDraft = $this->contentService->createContentDraft($object->getProperty('content_info'));

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $this->mapObjectToUpdateStruct($object, $contentUpdateStruct);

        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $content = $this->contentService->publishVersion($contentDraft->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::update'));
        }

        $object->setProperty('id', $content->contentInfo->id);
        $object->setProperty('version_info', $content->versionInfo);
        $object->setProperty('content_info', $content->contentInfo);

        // Add/Update/Delete parent locations
        $this->locationManager->syncronizeLocationsFromContentObject($object);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new UnsupportedObjectOperationException(ContentObject::class, get_class($object));
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
        if (!$object instanceof ContentObject) {
            throw new UnsupportedObjectOperationException(ContentObject::class, get_class($object));
        }

        try {
            $content = $this->find($object);
            $this->contentService->deleteContent($content->contentInfo);

            return true;
        } catch (NotFoundException $notFound) {
            return false;
        }
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

        $object->setProperty('main_location_id', $location->id);

        return $this->contentService->updateContentMetadata($object->getProperty('content_info'), $contentMetadataUpdateStruct);
    }

    /**
     * Maps object data to create struct.
     *
     * @param ContentObject       $object       Content object to map from
     * @param ContentCreateStruct $createStruct Content create struct to map to
     *
     * @throws \InvalidArgumentException
     */
    private function mapObjectToContentStruct(ContentObject $object, ContentCreateStruct $createStruct)
    {
        $this->assignStructFieldValues($object, $createStruct);

        if ($object->getProperty('language')) {
            $createStruct->mainLanguageCode = $object->getProperty('language');
        }

        if ($object->getProperty('remote_id')) {
            $createStruct->remoteId = $object->getProperty('remote_id');
        }
    }

    /**
     * Maps object data to update struct.
     *
     * @param ContentObject       $object              Content object to map from
     * @param ContentUpdateStruct $contentUpdateStruct Content update struct to map to
     *
     * @throws \InvalidArgumentException
     */
    private function mapObjectToUpdateStruct(ContentObject $object, ContentUpdateStruct $contentUpdateStruct)
    {
        $this->assignStructFieldValues($object, $contentUpdateStruct);
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
            if (is_array($value)) {
                $value = end($value);
            }

            $struct->setField($key, $value);
        }
    }
}
