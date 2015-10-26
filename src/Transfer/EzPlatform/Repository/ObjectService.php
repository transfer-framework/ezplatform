<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository;

use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;

/**
 * Object service.
 */
class ObjectService extends AbstractRepositoryService
{
    /**
     * @var ContentManager Content manager.
     */
    private $contentManager;

    /**
     * @var LocationManager Location manager.
     */
    private $locationManager;

    /**
     * Returns content manager.
     *
     * @return Manager\ContentManager
     */
    public function getContentManager()
    {
        if ($this->contentManager != null) {
            return $this->contentManager;
        }

        $this->contentManager = new Manager\ContentManager($this->repository);
        $this->contentManager->setLogger($this->logger);

        return $this->contentManager;
    }

    /**
     * Returns location manager.
     *
     * @return Manager\LocationManager
     */
    public function getLocationManager()
    {
        if ($this->locationManager != null) {
            return $this->locationManager;
        }

        $this->locationManager = new Manager\LocationManager($this->repository);
        $this->locationManager->setLogger($this->logger);

        return $this->locationManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        if ($object instanceof ContentObject) {
            return $this->getContentManager()->createOrUpdate($object);
        }
    }

    /**
     * Tests whether an object is new.
     *
     * @param ObjectInterface $object Object to test.
     *
     * @return bool True, if new
     */
    public function isNew($object)
    {
        if ($object instanceof ContentObject) {
            return $this->getContentManager()->isNew($object);
        }

        return false;
    }
}
