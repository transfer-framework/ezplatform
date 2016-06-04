<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Values\Mapper;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use Transfer\EzPlatform\Repository\Values\UserObject;

/**
 * User mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class UserMapper
{
    /**
     * @var UserObject
     */
    public $userObject;

    /**
     * @param UserObject $userObject
     */
    public function __construct(UserObject $userObject)
    {
        $this->userObject = $userObject;
    }

    /**
     * @param User $user
     */
    public function userToObject(User $user)
    {
        $this->userObject->data['username'] = $user->login;
        $this->userObject->data['email'] = $user->email;
        $this->userObject->data['enabled'] = $user->enabled;
        $this->userObject->data['max_login'] = $user->maxLogin;
    }

    /**
     * @param UserCreateStruct $userCreateStruct
     */
    public function getNewUserCreateStruct(UserCreateStruct $userCreateStruct)
    {
        if (isset($this->userObject->data['enabled'])) {
            $userCreateStruct->enabled = $this->userObject->data['enabled'];
        }

        $fields = array_flip($this->userObject->data['fields']);
        array_walk($fields, array($userCreateStruct, 'setField'));
    }

    /**
     * @param UserUpdateStruct $userUpdateStruct
     */
    public function getNewUserUpdateStruct(UserUpdateStruct $userUpdateStruct)
    {
        $userUpdateStruct->email = $this->userObject->data['email'];

        if (isset($this->userObject->data['max_login'])) {
            $userUpdateStruct->maxLogin = $this->userObject->data['max_login'];
        }

        if (isset($this->userObject->data['enabled'])) {
            $userUpdateStruct->enabled = $this->userObject->data['enabled'];
        }

        if (isset($this->userObject->data['fields'])) {
            $userUpdateStruct->contentUpdateStruct = new ContentUpdateStruct();
            $fields = array_flip($this->userObject->data['fields']);
            array_walk($fields, array($userUpdateStruct->contentUpdateStruct, 'setField'));
        }
    }
}
