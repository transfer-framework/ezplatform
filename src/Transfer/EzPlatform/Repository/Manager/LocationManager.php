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
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
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
     * {@inheritdoc}
     */
    public function find(ValueObject $object)
    {
        try {
            if (isset($object->data['remote_id'])) {
                $location = $this->locationService->loadLocationByRemoteId($object->data['remote_id']);
            } elseif ($object->getProperty('id')) {
                $location = $this->locationService->loadLocation($object->getProperty('id'));
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($location)) {
            throw new ObjectNotFoundException(Location::class, array('remote_id', 'id'));
        }

        return $location;
    }

    /**
     * Shortcut to find, mainly for locating parents.
     *
     * @param int $id
     *
     * @return Location
     *
     * @throws ObjectNotFoundException
     */
    public function findById($id)
    {
        return $this->find(new ValueObject([], ['id' => $id]));
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof LocationObject) {
            throw new UnsupportedObjectOperationException(LocationObject::class, get_class($object));
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
            throw new UnsupportedObjectOperationException(LocationObject::class, get_class($object));
        }

        $location = $this->find($object);

        // Move if parent_location_id differs.
        if (isset($object->data['parent_location_id'])) {
            if ($object->data['parent_location_id'] !== $location->parentLocationId) {
                $parentLocation = $this->findById($object->data['parent_location_id']);
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
            throw new UnsupportedObjectOperationException(LocationObject::class, get_class($object));
        }

        try {
            $this->find($object);

            return $this->update($object);
        } catch (NotFoundException $notFound) {
            return $this->create($object);
        }
    }

    /**
     * Creates/updates/deletes locations in ContentObject->parent_locations.
     *
     * @param ContentObject $object
     */
    public function syncronizeLocationsFromContentObject(ContentObject $object)
    {
        /** @var LocationObject[] $parentLocations */
        $parentLocations = $object->getProperty('parent_locations');
        if (is_array($parentLocations) && count($parentLocations) > 0) {
            $addOrUpdate = [];
            foreach ($parentLocations as $parentLocation) {
                $addOrUpdate[$parentLocation->data['parent_location_id']] = $parentLocation;
            }

            // Filter which Locations should be created/updated and deleted.
            $delible = $this->filterLocationsToBeDeleted($object, $addOrUpdate);

            // Create or update locations, and attach to Content
            foreach ($addOrUpdate as $parentLocation) {
                $parentLocation->data['content_id'] = $object->getProperty('content_info')->id;
                $locationObject = $this->createOrUpdate($parentLocation);
                $object->addParentLocation($locationObject);
            }

            // Lastly delete, cannot delete first because Content cannot have zero locations.
            foreach ($delible as $delete) {
                $this->locationService->deleteLocation($delete);
            }
        }
    }

    /**
     * @param ContentObject    $object
     * @param LocationObject[] $locationsToKeep
     *
     * @return Location[]
     */
    private function filterLocationsToBeDeleted(ContentObject $object, $locationsToKeep)
    {
        $toBeDeleted = [];

        foreach ($this->locationService->loadLocations($object->getProperty('content_info')) as $existingLocation) {
            if (!array_key_exists($existingLocation->parentLocationId, $locationsToKeep)) {
                $toBeDeleted[] = $existingLocation;
            }
        }

        return $toBeDeleted;
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof LocationObject) {
            throw new UnsupportedObjectOperationException(LocationObject::class, get_class($object));
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
