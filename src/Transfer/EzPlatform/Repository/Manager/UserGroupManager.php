<?php

/**
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
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\FinderInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * User Group manager.
 *
 * @internal
 */
class UserGroupManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface, FinderInterface
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
     * {@inheritdoc}
     */
    public function find(ValueObject $object, $throwException = false)
    {
        try {
            if (isset($object->data['remote_id'])) {
                $contentObject = $this->contentService->loadContentByRemoteId($object->data['remote_id']);
                $userGroup = $this->userService->loadUserGroup($contentObject->contentInfo->id);
            } elseif ($object->getProperty('id')) {
                $userGroup = $this->userService->loadUserGroup($object->getProperty('id'));
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($userGroup)) {
            throw new ObjectNotFoundException(UserGroup::class, array('remote_id', 'id'));
        }

        return $userGroup;
    }

    /**
     * Shortcut to get UserGroup by id, mainly to get parent by Id.
     *
     * @param int  $id
     * @param bool $throwException
     *
     * @return UserGroup|false
     *
     * @throws NotFoundException
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
        if (!$object instanceof UserGroupObject) {
            throw new UnsupportedObjectOperationException(UserGroupObject::class, get_class($object));
        }

        $parentUserGroup = $this->findById($object->data['parent_id'], true);

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

        $object->getMapper()->userGroupToObject($userGroup);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            throw new UnsupportedObjectOperationException(UserGroupObject::class, get_class($object));
        }

        $userGroup = $this->find($object, true);

        $userGroupUpdateStruct = $this->userService->newUserGroupUpdateStruct();
        $userGroupUpdateStruct->contentUpdateStruct = $this->contentService->newContentUpdateStruct();

        $object->getMapper()->populateUserGroupUpdateStruct($userGroupUpdateStruct);

        $userGroup = $this->userService->updateUserGroup($userGroup, $userGroupUpdateStruct);

        if ($userGroup->parentId !== $object->data['parent_id']) {
            $newParentGroup = $this->findById($object->data['parent_id'], true);
            $this->userService->moveUserGroup($userGroup, $newParentGroup);
            $userGroup = $this->find($object, true);
        }

        $object->getMapper()->userGroupToObject($userGroup);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            throw new UnsupportedObjectOperationException(UserGroupObject::class, get_class($object));
        }

        try {
            $this->find($object);

            return $this->update($object);
        } catch (NotFoundException $notFound) {
            return $this->create($object);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function remove(ObjectInterface $object)
    {
        if (!$object instanceof UserGroupObject) {
            throw new UnsupportedObjectOperationException(UserGroupObject::class, get_class($object));
        }

        try {
            $userGroup = $this->find($object);
            $this->userService->deleteUserGroup($userGroup);

            return true;
        } catch (NotFoundException $notFound) {
            return false;
        }
    }
}
