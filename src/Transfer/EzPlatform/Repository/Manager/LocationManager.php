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
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Values\LocationObject;
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
     * @param ValueObject $object
     * @param bool        $throwException
     *
     * @return Location|false
     *
     * @throws NotFoundException
     */
    public function find(ValueObject $object, $throwException = false)
    {
        try {
            if (isset($object->data['remote_id'])) {
                $location = $this->locationService->loadLocationByRemoteId($object->data['remote_id']);
            } elseif ($object->getProperty('id')) {
                $location = $this->locationService->loadLocation($object->getProperty('id'));
            }
        } catch (NotFoundException $notFound) {
            $exception = $notFound;
        }

        if (!isset($location)) {
            if (isset($exception) && $throwException) {
                throw $exception;
            }

            return false;
        }

        return isset($location) ? $location : false;
    }

    /**
     * Shortcut to find, mainly for locating parents.
     *
     * @param int  $id
     * @param bool $throwException
     *
     * @return Location|false
     */
    public function findById($id, $throwException = false)
    {
        return $this->find(new ValueObject([], ['id' => $id]), $throwException);
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

        $location = $this->find($object, true);

        // Move if parent_location_id differs.
        if (isset($object->data['parent_location_id'])) {
            if ($object->data['parent_location_id'] !== $location->parentLocationId) {
                $parentLocation = $this->findById($object->data['parent_location_id'], true);
                $this->locationService->moveSubtree($location, $parentLocation);
            }
        }

        $locationUpdateStruct = $this->locationService->newLocationUpdateStruct();

        $object->getMapper()->getNewLocationUpdateStruct($locationUpdateStruct);

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
