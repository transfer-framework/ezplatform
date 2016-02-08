<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Exception\UserGroupNotFoundException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * User Group manager.
 *
 * @internal
 */
class UserGroupManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @param Repository $repository
     */
    public function __construct(Repository $repository)
    {
        $this->repository = $repository;
        $this->userService = $repository->getUserService();
        $this->contentService = $repository->getContentService();
        $this->contentTypeService = $repository->getContentTypeService();
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Shortcut to load a usergroup by id, without throwing an exception if it's not found.
     *
     * @param int $id
     *
     * @return UserGroup|false
     */
    public function find($id)
    {
        try {
            return $this->userService->loadUserGroup($id);
        } catch (NotFoundException $e) {
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            return;
        }

        $parentUserGroup = $this->find($object->data['parent_id']);

        if (!$parentUserGroup) {
            throw new UserGroupNotFoundException(sprintf('Usergroup with parent_id "%s" not found.', $object->data['parent_id']));
        }

        // Instantiate usergroup
        $contentType = $this->contentTypeService->loadContentTypeByIdentifier($object->data['content_type_identifier']);
        $userGroupCreateStruct = $this->userService->newUserGroupCreateStruct(
            $object->data['main_language_code'],
            $contentType
        );

        // Populate usergroup fields
        $object->getMapper()->populateUserGroupCreateStruct($userGroupCreateStruct);

        // Create usergroup
        $userGroup = $this->userService->createUserGroup($userGroupCreateStruct, $parentUserGroup);
        $object->data['id'] = $userGroup->id;

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            return;
        }

        if (!array_key_exists('id', $object->data)) {
            throw new UserGroupNotFoundException('Unable to update usergroup without an id.');
        }

        $userGroup = $this->find($object->data['id']);

        if (!$userGroup) {
            throw new UserGroupNotFoundException(sprintf('Usergroup with id "%s" not found.', $object->data['id']));
        }

        $userGroupUpdateStruct = $this->userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        $object->getMapper()->populateUserGroupUpdateStruct($userGroupUpdateStruct);

        $this->userService->updateUserGroup($userGroup, $userGroupUpdateStruct);

        if ($userGroup->parentId !== $object->data['parent_id']) {
            $newParentGroup = $this->find($object->data['parent_id']);
            $this->userService->moveUserGroup($userGroup, $newParentGroup);
        }

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            return;
        }

        if (array_key_exists('id', $object->data)) {
            $userGroup = $this->find($object->data['id']);
        }
        if (!isset($userGroup) || false === $userGroup) {
            return $this->create($object);
        } else {
            return $this->update($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            return;
        }

        if (array_key_exists('id', $object->data)) {
            $userGroup = $this->find($object->data['id']);
        } else {
            throw new UserGroupNotFoundException(sprintf('Usergroup with id "%s" not found.', ''));
        }

        if (!$userGroup) {
            throw new UserGroupNotFoundException(sprintf('Usergroup with id "%s" not found.', $object->data['id']));
        }

        $this->userService->deleteUserGroup($userGroup);

        return true;
    }
}
