<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Exception\UnsupportedOperationException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;

/**
 * Location manager.
 *
 * @internal
 */
class LocationManager implements LoggerAwareInterface, CreatorInterface, RemoverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface Logger
     */
    protected $logger;

    /**
     * @var LocationService Location service
     */
    private $locationService;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;

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
    public function create(ObjectInterface $object)
    {
        throw new UnsupportedOperationException('Location creation is not supported at the moment.');
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        throw new UnsupportedOperationException('Location removal is not supported at the moment.');
    }

    /**
     * Hides a location.
     *
     * @param LocationObject $object Location object
     *
     * @return Location
     */
    public function hide(LocationObject $object)
    {
        return $this->locationService->hideLocation($object->data);
    }

    /**
     * Un-hides a location.
     *
     * @param LocationObject $object Location object
     *
     * @return Location
     */
    public function unHide(LocationObject $object)
    {
        return $this->locationService->unhideLocation($object->data);
    }

    /**
     * Toggles location visibility.
     *
     * @param LocationObject $object Location object
     *
     * @return Location
     */
    public function toggleVisibility(LocationObject $object)
    {
        /** @var Location $location */
        $location = $object->data;

        if ($location->hidden) {
            return $this->unHide($object);
        }

        return $this->hide($object);
    }
}
