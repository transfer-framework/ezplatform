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
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use eZ\Publish\API\Repository\Values\Content\Query;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Exception\MissingIdentificationPropertyException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
use eZ\Publish\API\Repository\Values\Content\Query\Criterion;

/**
 * Content manager.
 *
 * @internal
 */
class ContentManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
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
     * @param Repository $repository
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
     * Finds a content object by content ID or remote ID.
     *
     * @param ContentObject $object
     *
     * @return ContentObject|false
     *
     * @throws NotFoundException
     * @throws \Transfer\EzPlatform\Exception\InvalidDataStructureException
     */
    public function find(ContentObject $object)
    {
        if($object->getProperty('id')) {
            try {
                $content = $this->contentService->loadContent($object->getProperty('id'));
            } catch (NotFoundException $notFoundException) {
                // We'll store if for now, and throw it later if needed
                $e = $notFoundException;
            }
        }elseif($object->getProperty('remote_id')) {
            try {
                $content = $this->contentService->loadContentByRemoteId($object->getProperty('remote_id'));
            } catch (NotFoundException $notFoundException) {
                // We'll throw it later if needed
                $e = $notFoundException;
            }
        }else{
            return false;
        }

        if(!isset($content) && isset($e)) {
            throw $e;
        }

        $object = new ContentObject(array());
        $object->getMapper()->contentToObject($content);
        
        if($content->contentInfo->published) {
            $locations = $this->locationService->loadLocations($content->contentInfo);
            $object->setParentLocations($locations);
        }

        $type = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $object->setProperty('content_type_identifier', $type->identifier);
        $object->setParentLocations($this->locationService->loadLocations($content->contentInfo));


        return $object;
    }

    public function findByRemoteId($remoteId)
    {
        return $this->find(new ContentObject([],['remote_id' => $remoteId]));
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new \InvalidArgumentException('Object is not supported for creation.');
        }

        $createStruct = $this->contentService->newContentCreateStruct(
            $this->contentTypeService->loadContentTypeByIdentifier($object->getProperty('content_type_identifier')),
            $object->getProperty('language')
        );

        $this->mapObjectToContentStruct($object, $createStruct);

        /** @var LocationObject[] $locationObjects */
        $locationObjects = $object->getProperty('parent_locations');
        $locationCreateStructs = [];
        if(is_array($locationObjects) && count($locationObjects) > 0) {
            foreach($locationObjects as $locationObject) {
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
            throw new \InvalidArgumentException('Object is not supported for update.');
        }

        $existingContent = $this->find($object);
        if (null === $object->getProperty('content_info')) {
            $object->setProperty('content_info', $existingContent->getProperty('content_info'));
        }

        $contentDraft = $this->contentService->createContentDraft($object->getProperty('content_info'));

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $this->mapObjectToUpdateStruct($object, $contentUpdateStruct);

        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $content = $this->contentService->publishVersion($contentDraft->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::update'));
        }

        /** @var LocationObject[] $locationObjects */
        $locationObjects = $object->getProperty('parent_locations');
        if(is_array($locationObjects) && count($locationObjects) > 0) {

            $addOrUpdate = [];
            foreach($locationObjects as $locationObject) {
                $addOrUpdate[$locationObject->data['parent_location_id']] = $locationObject;
            }

            $existingLocations = [];
            foreach($this->locationService->loadLocations($object->getProperty('content_info')) as $existingLocation) {
                if (!array_key_exists($existingLocation->parentLocationId, $addOrUpdate)) {
                    $this->locationService->deleteLocation($existingLocation);
                } else {
                    $existingLocations[$existingLocation->parentLocationId] = $existingLocation;
                }
            }

            foreach($addOrUpdate as $locationObject) {
                if(!array_key_exists($locationObject->data['parent_location_id'], $existingLocations)) {
                    // create or update
                    $locationObject->data['content_id'] = $content->id;
                    $locationObject = $this->locationManager->createOrUpdate($locationObject);
                    $object->addParentLocation($locationObject);
                }
            }
        }

        $object->setProperty('version_info', $content->versionInfo);
        $object->setProperty('content_info', $content->contentInfo);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new \InvalidArgumentException('Object is not supported for creation or update.');
        }

        if (!$object->getProperty('content_id') && !$object->getProperty('remote_id')) {
            throw new MissingIdentificationPropertyException($object);
        }

        try {
            if($this->find($object)) {
                return $this->update($object);
            }
        } catch(NotFoundException $notFoundException) {
            // Catch and ignore, we'll create it instead.
        }

        return $this->create($object);
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new \InvalidArgumentException('Object is not supported for deletion.');
        }

        $object = $this->findByRemoteId($object->getProperty('remote_id'));

        if ($object instanceof ContentObject && $object->getProperty('content_info')) {
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
