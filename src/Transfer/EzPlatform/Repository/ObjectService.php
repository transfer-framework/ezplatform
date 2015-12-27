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
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\LanguageObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
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
     * @var ContentTypeManager
     */
    private $contentTypeManager;

    /**
     * @var LanguageManager
     */
    private $languageManager;

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

        if ($this->logger) {
            $this->contentManager->setLogger($this->logger);
        }

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
        if ($this->logger) {
            $this->locationManager->setLogger($this->logger);
        }

        return $this->locationManager;
    }

    /**
     * Returns contenttype manager.
     *
     * @return ContentTypeManager
     */
    public function getContentTypeManager()
    {
        if ($this->contentTypeManager != null) {
            return $this->contentTypeManager;
        }

        $this->contentTypeManager = new Manager\ContentTypeManager($this->repository, $this->getLanguageManager());
        if ($this->logger) {
            $this->contentTypeManager->setLogger($this->logger);
        }

        return $this->contentTypeManager;
    }

    /**
     * Returns language manager.
     *
     * @return LanguageManager
     */
    public function getLanguageManager()
    {
        if ($this->languageManager != null) {
            return $this->languageManager;
        }

        $this->languageManager = new Manager\LanguageManager($this->repository);
        if ($this->logger) {
            $this->languageManager->setLogger($this->logger);
        }

        return $this->languageManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        if ($object instanceof ContentObject) {
            return $this->getContentManager()->createOrUpdate($object);
        } elseif ($object instanceof ContentTypeObject) {
            return $this->getContentTypeManager()->createOrUpdate($object);
        } elseif ($object instanceof LanguageObject) {
            return $this->getLanguageManager()->add($object);
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
