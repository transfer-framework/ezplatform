<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;

/**
 * Location manager.
 *
 * @internal
 */
class LocationManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
{
    /**
     * @var LoggerInterface Logger
     */
    protected $logger;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LocationService Location service
     */
    private $locationService;

    /**
     * @var ContentService Content service
     */
    private $contentService;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->locationService = $repository->getLocationService();
        $this->contentService = $repository->getContentService();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Attempts to load Location based on id or remoteId.
     * Returns false if not found.
     *
     * @param LocationObject $object
     *
     * @return false|Location
     */
    public function find(LocationObject $object)
    {
        try {
            if (isset($object->data['id'])) {
                $location = $this->locationService->loadLocation($object->data['id']);
            } elseif (isset($object->data['remote_id'])) {
                $location = $this->locationService->loadLocationByRemoteId($object->data['remote_id']);
            }
        } catch (NotFoundException $notFound) {
            return false;
        }

        return isset($location) ? $location : false;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof LocationObject) {
            return;
        }

        $contentInfo = $this->repository->getContentService()->loadContentInfo($object->data['content_id']);
        $locationCreateStruct = $this->locationService->newLocationCreateStruct($object->data['parent_location_id']);

        $object->getMapper()->getNewLocationCreateStruct($locationCreateStruct);

        $location = $this->locationService->createLocation($contentInfo, $locationCreateStruct);

        if ($this->logger) {
            $this->logger->info(sprintf('Created location %s on content id %s, with parent location id %s.', $location->id, $contentInfo->id, $location->parentLocationId));
        }

        $object->getMapper()->locationToObject($location);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof LocationObject) {
            return;
        }

        $locationUpdateStruct = $this->locationService->newLocationUpdateStruct();

        $object->getMapper()->getNewLocationUpdateStruct($locationUpdateStruct);

        $location = $this->locationService->loadLocationByRemoteId($object->data['remote_id']);

        $location = $this->locationService->updateLocation($location, $locationUpdateStruct);

        if ($this->logger) {
            $this->logger->info(sprintf('Updated location %s with parent location id %s.', $location->id, $location->parentLocationId));
        }

        $object->getMapper()->locationToObject($location);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof LocationObject) {
            return;
        }

        if ($this->find($object)) {
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
        if (!$object instanceof LocationObject) {
            return;
        }

        if ($location = $this->find($object)) {
            $this->locationService->deleteLocation($location);
        }

        return true;
    }

    /**
     * Hides a location.
     *
     * @param Location $location
     *
     * @return Location
     */
    public function hide(Location $location)
    {
        return $this->locationService->hideLocation($location);
    }

    /**
     * Un-hides a location.
     *
     * @param Location $location
     *
     * @return Location
     */
    public function unHide(Location $location)
    {
        return $this->locationService->unhideLocation($location);
    }

    /**
     * Toggles location visibility.
     *
     * @param Location $location
     *
     * @return Location
     */
    public function toggleVisibility(Location $location)
    {
        if ($location->hidden) {
            return $this->unHide($location);
        }

        return $this->hide($location);
    }
}
