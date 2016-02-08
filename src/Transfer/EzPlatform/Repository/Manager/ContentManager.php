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
use eZ\Publish\API\Repository\Values\Content\Query;
use eZ\Publish\Core\Repository\Values\Content\TrashItem;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\ContentObject;
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
        } catch (\Exception $e) {
            return;
        }

        $object = new ContentObject($content->fields);
        $object->setContentInfo($content->contentInfo);

        $type = $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId);
        $object->setContentType($type->identifier);

        return $object;
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

        $this->mapObjectToStruct($object, $createStruct);

        $content = $this->contentService->createContent($createStruct);
        $this->contentService->publishVersion($content->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::create'));
        }

        $object->setVersionInfo($content->versionInfo);
        $object->setContentInfo($content->contentInfo);

        $this->created[] = $object;

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

        $existingContent = $this->findByRemoteId($object->getRemoteId());
        if (null === $object->getProperty('content_info')) {
            $object->setProperty('content_info', $existingContent->getContentInfo());
        }

        $this->ensureNotTrashed($object);

        $contentDraft = $this->contentService->createContentDraft($object->getProperty('content_info'));

        $contentUpdateStruct = $this->contentService->newContentUpdateStruct();
        $this->mapObjectToUpdateStruct($object, $contentUpdateStruct);

        $contentDraft = $this->contentService->updateContent($contentDraft->versionInfo, $contentUpdateStruct);
        $content = $this->contentService->publishVersion($contentDraft->versionInfo);

        if ($this->logger) {
            $this->logger->info(sprintf('Published new version of %s', $object->getProperty('name')), array('ContentManager::update'));
        }

        $object->setVersionInfo($content->versionInfo);
        $object->setContentInfo($content->contentInfo);

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

        if ($this->findByRemoteId($object->getRemoteId())) {
            return $this->update($object);
        } else {
            return $this->create($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof ContentObject) {
            throw new \InvalidArgumentException('Object is not supported for deletion.');
        }

        $object = $this->findByRemoteId($object->getRemoteId());

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

        $object->setMainLocationId($location->id);

        return $this->contentService->updateContentMetadata($object->getContentInfo(), $contentMetadataUpdateStruct);
    }

    /**
     * Maps object data to create struct.
     *
     * @param ContentObject       $object       Content object to map from
     * @param ContentCreateStruct $createStruct Content create struct to map to
     *
     * @throws \InvalidArgumentException
     */
    private function mapObjectToStruct(ContentObject $object, ContentCreateStruct $createStruct)
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

    /**
     * @param ContentObject $object
     * @param Location      $location
     */
    private function ensureNotTrashed(ContentObject $object)
    {
        $query = new Query();
        $query->filter = new Criterion\ContentId($object->getContentInfo()->id);
        $trash = $this->repository->getTrashService()->findTrashItems($query);
        if ($trash->count > 0) {
            /** @var TrashItem $trashItem */
            $trashItem = $trash->items[0];
            $parentLocation = $this->repository->getLocationService()->loadLocation($trashItem->parentLocationId);
            $this->repository->getTrashService()->recover($trashItem, $parentLocation);
            if ($this->logger) {
                $this->logger->warning(sprintf('Content with remote id %s was found in the trash, recovering it and continues the proccess. '.
                    'Please check if this is correct, and prevent it from happening again.', $object->getRemoteId()));
            }
        }
    }
}
