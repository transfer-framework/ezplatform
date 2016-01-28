<?php

/*
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
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Data\UserObject;
use Transfer\EzPlatform\Exception\UserNotFoundException;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;
use Transfer\EzPlatform\Repository\Manager\Type\RemoverInterface;
use Transfer\EzPlatform\Repository\Manager\Type\UpdaterInterface;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * User manager.
 *
 * @internal
 */
class UserManager implements LoggerAwareInterface, CreatorInterface, UpdaterInterface, RemoverInterface
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
     * Finds user object by username.
     *
     * @param string $username
     *
     * @return User|false
     */
    public function findByUsername($username)
    {
        if (!is_string($username)) {
            return false;
        }

        try {
            $user = $this->userService->loadUserByLogin($username);
        } catch (NotFoundException $e) {
            return false;
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function create(ObjectInterface $object)
    {
        if (!$object instanceof UserObject) {
            return;
        }

        $userCreateStruct = $this->userService->newUserCreateStruct(
            $object->data['username'],
            $object->data['email'],
            $object->data['password'],
            $object->data['main_language_code']
        );

        $object->getMapper()->getNewUserCreateStruct($userCreateStruct);

        $groups = [];
        foreach ($object->parents as $userGroup) {
            $userGroup = $this->userGroupManager->createOrUpdate($userGroup);
            if ($userGroup instanceof UserGroupObject) {
                $groups[] = $this->userGroupManager->find($userGroup->data['id']);
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
            return;
        }

        $user = $this->findByUsername($object->data['username']);

        if (!$user) {
            throw new UserNotFoundException(sprintf('User with username "%s" not found.', $object->data['username']));
        }

        // Populate struct
        $userUpdateStruct = $this->userService->newUserUpdateStruct();
        $object->getMapper()->getNewUserUpdateStruct($userUpdateStruct);

        // Update user
        $ezuser = $this->userService->updateUser($user, $userUpdateStruct);

        // Assign user to usergroups
        $ezUserGroups = $this->assignUserToUserGroups($ezuser, $object->parents);

        // Unassign user from usergroups
        $this->unassignUserFromUserGroups($ezuser, $ezUserGroups);

        return $object;
    }

    /**
     * {@inheritdoc}
     */
    public function createOrUpdate(ObjectInterface $object)
    {
        if (!$object instanceof UserObject) {
            return;
        }

        if (!$this->findByUsername($object->data['username'])) {
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
        if (!$object instanceof UserObject) {
            return;
        }

        $user = $this->findByUsername($object->data['username']);

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
                $ezUserGroup = $this->userGroupManager->find($userGroup->data['id']);
                if ($ezUserGroup) {
                    $ezUserGroups[$ezUserGroup->id] = $ezUserGroup;
                    $this->userService->assignUserToUserGroup($user, $ezUserGroup);
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
