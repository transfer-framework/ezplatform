<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Core;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;

/**
 * Abstract repository service.
 *
 * @internal
 */
abstract class AbstractRepositoryService implements LoggerAwareInterface
{
    /**
     * @var Repository eZ Platform Repository
     */
    protected $repository;

    /**
     * @var LoggerInterface Logger
     */
    protected $logger;

    /**
     * @param Repository $repository eZ Platform Repository
     */
    public function __construct(Repository $repository)
    {
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
     * Sets current user based on `repository_current_user` option set in constructor.
     *
     * @param string $username Username
     */
    public function setCurrentUser($username)
    {
        $this->repository->setCurrentUser(
            $this->getUserService()->loadUserByLogin($username)
        );
    }

    /**
     * Return eZ Platform Repository.
     *
     * @return Repository
     */
    public function getRepository()
    {
        return $this->repository;
    }

    /**
     * Returns eZ Platform content type service.
     *
     * @return ContentTypeService
     */
    public function getContentTypeService()
    {
        return $this->repository->getContentTypeService();
    }

    /**
     * Returns eZ Platform content service.
     *
     * @return ContentService
     */
    public function getContentService()
    {
        return $this->repository->getContentService();
    }

    /**
     * Returns eZ Platform location service.
     *
     * @return LocationService
     */
    public function getLocationService()
    {
        return $this->repository->getLocationService();
    }

    /**
     * Returns eZ Platform user service.
     *
     * @return UserService
     */
    public function getUserService()
    {
        return $this->repository->getUserService();
    }

    /**
     * Handles object creation and update.
     *
     * @param ObjectInterface $object
     */
    abstract public function createOrUpdate($object);

    /**
     * Handles object deletion.
     *
     * @param ObjectInterface $object
     */
    abstract public function remove($object);
}
