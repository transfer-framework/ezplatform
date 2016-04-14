<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository;

use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\LanguageObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Data\UserObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Repository\Manager\UserGroupManager;
use Transfer\EzPlatform\Repository\Manager\UserManager;

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
     * @var UserGroupManager
     */
    private $userGroupManager;

    /**
     * @var UserManager
     */
    private $userManager;

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

        $this->contentManager = new Manager\ContentManager($this->repository, $this->getLocationManager());

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
     * Returns user group manager.
     *
     * @return UserGroupManager
     */
    public function getUserGroupManager()
    {
        if ($this->userGroupManager != null) {
            return $this->userGroupManager;
        }

        $this->userGroupManager = new Manager\UserGroupManager($this->repository);
        if ($this->logger) {
            $this->userGroupManager->setLogger($this->logger);
        }

        return $this->userGroupManager;
    }

    /**
     * Returns user manager.
     *
     * @return UserManager
     */
    public function getUserManager()
    {
        if ($this->userManager != null) {
            return $this->userManager;
        }

        $this->userManager = new Manager\UserManager($this->repository, $this->getUserGroupManager());
        if ($this->logger) {
            $this->userManager->setLogger($this->logger);
        }

        return $this->userManager;
    }

    /**
     * {@inheritdoc}
     */
    public function create($object)
    {
        $map = array(
            ContentObject::class => array($this, 'getContentManager'),
            LocationObject::class => array($this, 'getLocationManager'),
            ContentTypeObject::class => array($this, 'getContentTypeManager'),
            LanguageObject::class => array($this, 'getLanguageManager'),
            UserObject::class => array($this, 'getUserManager'),
            UserGroupObject::class => array($this, 'getUserGroupManager'),
        );

        foreach ($map as $class => $callable) {
            if ($object instanceof $class) {
                
                $manager = call_user_func($callable);
                return $manager->createOrUpdate($object);
            }
        }

        throw new \InvalidArgumentException('Object is not supported for creation.');
    }
}
