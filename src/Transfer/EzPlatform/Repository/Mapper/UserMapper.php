<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Mapper;

use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserUpdateStruct;
use eZ\Publish\Core\Repository\Values\Content\ContentUpdateStruct;
use Transfer\EzPlatform\Data\UserObject;

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
     * @param UserCreateStruct $userCreateStruct
     */
    public function getNewUserCreateStruct(UserCreateStruct $userCreateStruct)
    {
        $userCreateStruct->enabled = $this->userObject->data['enabled'];

        $fields = array_flip($this->userObject->data['fields']);
        array_walk($fields, array($userCreateStruct, 'setField'));
    }

    /**
     * @param UserUpdateStruct $userUpdateStruct
     */
    public function getNewUserUpdateStruct(UserUpdateStruct $userUpdateStruct)
    {
        $userUpdateStruct->email = $this->userObject->data['email'];
        $userUpdateStruct->maxLogin = $this->userObject->data['max_login'];
        $userUpdateStruct->enabled = $this->userObject->data['enabled'];

        if (isset($this->userObject->data['fields'])) {
            $userUpdateStruct->contentUpdateStruct = new ContentUpdateStruct();
            $fields = array_flip($this->userObject->data['fields']);
            array_walk($fields, array($userUpdateStruct->contentUpdateStruct, 'setField'));
        }
    }
}
