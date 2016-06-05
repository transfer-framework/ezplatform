<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Manager;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\UserService;
use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserGroup;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Transfer\Data\ObjectInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\FinderInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Exceptions\InvalidArgumentException;

/**
 * User manager.
 *
 * @internal
 */
class UserManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface, FinderInterface
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
     * @var UserGroupManager
     */
    private $userGroupManager;

    /**
     * @param Repository       $repository
     * @param UserGroupManager $userGroupManager
     */
    public function __construct(Repository $repository, UserGroupManager $userGroupManager)
    {
        $this->repository = $repository;
        $this->userService = $repository->getUserService();
        $this->userGroupManager = $userGroupManager;
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
            if (isset($object->data['username'])) {
                $user = $this->userService->loadUserByLogin($object->data['username']);
            }
        } catch (NotFoundException $notFoundException) {
            // We'll throw our own exception later instead.
        }

        if (!isset($user)) {
            throw new ObjectNotFoundException(User::class, array('username'));
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof UserObject) {
            throw new UnsupportedObjectOperationException(UserObject::class, get_class($object));
        }

        $userCreateStruct = $this->userService->newUserCreateStruct(
            $object->data['username'],
            $object->data['email'],
            $object->data['password'],
            $object->data['main_language_code']
        );

        $object->getMapper()->mapObjectToCreateStruct($userCreateStruct);

        $groups = [];
        foreach ($object->data['parents'] as $userGroup) {
            $userGroup = $this->userGroupManager->createOrUpdate($userGroup);
            if ($userGroup instanceof UserGroupObject) {
                $groups[] = $this->userGroupManager->find($userGroup);
            }
        }

        $user = $this->userService->createUser($userCreateStruct, $groups);
        $object->data['id'] = $user->getUserId();

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function update(ObjectInterface $object)
    {
        if (!$object instanceof UserObject) {
            throw new UnsupportedObjectOperationException(UserObject::class, get_class($object));
        }

        $user = $this->find($object);

        // Populate struct
        $userUpdateStruct = $this->userService->newUserUpdateStruct();
        $object->getMapper()->mapObjectToUpdateStruct($userUpdateStruct);

        // Update user
        $user = $this->userService->updateUser($user, $userUpdateStruct);

        // Assign user to usergroups
        $userGroups = $this->assignUserToUserGroups($user, $object->data['parents']);

        // Unassign user from usergroups
        $this->unassignUserFromUserGroups($user, $userGroups);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof UserObject) {
            throw new UnsupportedObjectOperationException(UserObject::class, get_class($object));
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
        if (!$object instanceof UserObject) {
            throw new UnsupportedObjectOperationException(UserObject::class, get_class($object));
        }

        $user = $this->find($object);

        if ($user) {
            $this->userService->deleteUser($user);
        }

        return true;
    }

    /**
     * Assigns a collection of Transfer user groups from an eZ user, and returns the once who were added.
     *
     * @param User              $user
     * @param UserGroupObject[] $userGroupObjects
     *
     * @return UserGroup[]
     */
    protected function assignUserToUserGroups(User $user, array $userGroupObjects)
    {
        $ezUserGroups = [];
        foreach ($userGroupObjects as $userGroup) {
            $userGroup = $this->userGroupManager->createOrUpdate($userGroup);
            if ($userGroup instanceof UserGroupObject) {
                $ezUserGroup = $this->userGroupManager->find($userGroup);
                if ($ezUserGroup) {
                    $ezUserGroups[$ezUserGroup->id] = $ezUserGroup;
                    try {
                        $this->userService->assignUserToUserGroup($user, $ezUserGroup);
                    } catch (InvalidArgumentException $alreadyAssignedException) {
                        // Ignore error about: user already assigned to usergroup.
                    }
                }
            }
        }

        return $ezUserGroups;
    }

    /**
     * Unassigns a collection of eZ UserGroups from an eZ User.
     *
     * @param User        $user
     * @param UserGroup[] $userGroups
     */
    protected function unassignUserFromUserGroups(User $user, array $userGroups)
    {
        $existingUserGroups = $this->userService->loadUserGroupsOfUser($user);
        foreach ($existingUserGroups as $existingUserGroup) {
            if (!array_key_exists($existingUserGroup->id, $userGroups)) {
                $this->userService->unAssignUserFromUserGroup($user, $existingUserGroup);
            }
        }
    }
}
