<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Core;

use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\Repository\Values\LanguageObject;
use Transfer\EzPlatform\Repository\Values\LocationObject;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
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
     * @return ContentManager
     */
    public function getContentManager()
    {
        if ($this->contentManager != null) {
            return $this->contentManager;
        }

        $this->contentManager = new ContentManager($this->repository, $this->getLocationManager());

        if ($this->logger) {
            $this->contentManager->setLogger($this->logger);
        }

        return $this->contentManager;
    }

    /**
     * Returns location manager.
     *
     * @return LocationManager
     */
    public function getLocationManager()
    {
        if ($this->locationManager != null) {
            return $this->locationManager;
        }

        $this->locationManager = new LocationManager($this->repository);

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

        $this->contentTypeManager = new ContentTypeManager($this->repository, $this->getLanguageManager());
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

        $this->languageManager = new LanguageManager($this->repository);

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

        $this->userGroupManager = new UserGroupManager($this->repository);

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

        $this->userManager = new UserManager($this->repository, $this->getUserGroupManager());

        if ($this->logger) {
            $this->userManager->setLogger($this->logger);
        }

        return $this->userManager;
    }

    /**
     * @return array
     */
    protected function getManagerMapping()
    {
        return array(
            ContentObject::class => array($this, 'getContentManager'),
            LocationObject::class => array($this, 'getLocationManager'),
            ContentTypeObject::class => array($this, 'getContentTypeManager'),
            LanguageObject::class => array($this, 'getLanguageManager'),
            UserObject::class => array($this, 'getUserManager'),
            UserGroupObject::class => array($this, 'getUserGroupManager'),
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate($object)
    {
        foreach ($this->getManagerMapping() as $class => $callable) {
            if ($object instanceof $class) {
                /** @var UpdaterInterface $manager */
                $manager = call_user_func($callable);

                return $manager->createOrUpdate($object);
            }
        }

        throw new \InvalidArgumentException('Object is not supported for creation.');
    }

    /**
     * {@inheritdoc}
     */
    public function remove($object)
    {
        foreach ($this->getManagerMapping() as $class => $callable) {
            if ($object instanceof $class) {
                /** @var RemoverInterface $manager */
                $manager = call_user_func($callable);

                return $manager->remove($object);
            }
        }

        throw new \InvalidArgumentException('Object is not supported for deletion.');
    }
}
