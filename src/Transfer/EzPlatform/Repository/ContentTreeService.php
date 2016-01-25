<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationList;
use Transfer\Data\ObjectInterface;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;

/**
 * Content tree service.
 *
 * @internal
 */
class ContentTreeService extends AbstractRepositoryService
{
    /**
     * @var ObjectService Object service
     */
    protected $objectService;

    /**
     * @param Repository    $repository    eZ Platform Repository
     * @param ObjectService $objectService Object service
     */
    public function __construct($repository, $objectService)
    {
        parent::__construct($repository);

        $this->objectService = $objectService;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        if (!($object instanceof TreeObject)) {
            throw new \InvalidArgumentException('Invalid argument, expected object of type Transfer\Data\TreeObject');
        }

        $this->publishContentObjects($object);
        $this->publishLocations($object, $this->getLocationService()->loadLocation(
            $object->getProperty('location_id')
        ));
    }

    /**
     * Publishes content objects.
     *
     * @param TreeObject $object
     *
     * @throws \InvalidArgumentException
     */
    private function publishContentObjects(TreeObject $object)
    {
        $this->objectService->create($object->data);

        foreach ($object->getNodes() as $subObject) {
            if ($subObject instanceof TreeObject) {
                $this->publishContentObjects($subObject);
            } else {
                $this->objectService->create($subObject);
            }
        }
    }

    /**
     * Publishes locations.
     *
     * @param TreeObject $object
     * @param Location        $parentLocation
     *
     * @throws \InvalidArgumentException
     */
    private function publishLocations(TreeObject $object, Location $parentLocation)
    {
        /** @var Location $location */
        $location = $this->publishLocation($object->data, $parentLocation);

        foreach ($object->getNodes() as $subObject) {
            if ($subObject instanceof TreeObject) {
                $this->publishLocations($subObject, $location);
            } else {
                $this->publishLocation($subObject, $location);
            }
        }
    }

    /**
     * Publishes locations.
     *
     * @param ContentObject $object
     * @param Location      $parentLocation
     *
     * @return Location
     */
    private function publishLocation(ContentObject $object, Location $parentLocation)
    {
        /** @var LocationList $existingLocations */
        $existingLocations = $this->getLocationService()->loadLocationChildren($parentLocation);
        foreach ($existingLocations->locations as $location) {
            if ($location->contentInfo->id == $object->getContentInfo()->id) {
                if ($this->logger) {
                    $this->logger->info(
                        sprintf('Found existing location for %s (%s)', $object->getProperty('name'), implode('/', $location->path)),
                        array('SubtreeService::publishLocation')
                    );
                }

                $this->ensureLocationState($object, $location);

                return $location;
            }
        }

        $locationStruct = $this->getLocationService()->newLocationCreateStruct($parentLocation->id);

        if ($priority = $object->getPriority()) {
            $locationStruct->priority = $priority;
        }

        $location = $this->getLocationService()->createLocation($object->getContentInfo(), $locationStruct);

        if ($this->logger) {
            $this->logger->info(sprintf('Created location for %s (%s)', $object->getProperty('name'), implode('/', $location->path)), array('SubtreeService::publishLocation'));
        }

        $this->ensureLocationState($object, $location);

        return $location;
    }

    /**
     * Controls location state.
     *
     * @param ContentObject $object   Content object
     * @param Location      $location Location
     */
    private function ensureLocationState(ContentObject $object, $location)
    {
        $this->ensureMainLocationIdIsSet($object, $location);

        if ($object->isHidden() != $location->hidden) {
            $this->updateLocationVisibility($location);
        }
    }

    /**
     * Ensures that main location ID is set.
     *
     * @param ContentObject $object   Content object
     * @param Location      $location Location
     */
    private function ensureMainLocationIdIsSet(ContentObject $object, Location $location)
    {
        if (!$object->isMainObject()) {
            return;
        }

        if ($this->logger) {
            $this->logger->info(sprintf('Force main location id for %s', $object->getProperty('name')), array('SubtreeService::ensureMainLocationIdIsSet'));
        }

        $this->objectService->getContentManager()->setMainLocation($object, $location);
    }

    /**
     * Updates location visibility.
     *
     * @param Location $location
     */
    private function updateLocationVisibility(Location $location)
    {
        $this->objectService->getLocationManager()->toggleVisibility(new LocationObject($location));
    }
}
