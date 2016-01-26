<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Content;

use eZ\Publish\API\Repository\Values\User\UserGroupCreateStruct;
use eZ\Publish\API\Repository\Values\User\UserGroupUpdateStruct;
use Transfer\EzPlatform\Data\UserGroupObject;

/**
 * Usergroup mapper.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class UserGroupMapper
{
    /**
     * @var UserGroupObject
     */
    public $userGroupObject;

    /**
     * @param UserGroupObject $userGroupObject
     */
    public function __construct(UserGroupObject $userGroupObject)
    {
        $this->userGroupObject = $userGroupObject;
    }

    /**
     * @param UserGroupCreateStruct $userGroupCreateStruct
     */
    public function populateUserGroupCreateStruct(UserGroupCreateStruct $userGroupCreateStruct)
    {
        $fields = array_flip($this->userGroupObject->data['fields']);
        array_walk($fields, array($userGroupCreateStruct, 'setField'));
    }

    /**
     * @param UserGroupUpdateStruct $userGroupUpdateStruct
     */
    public function populateUserGroupUpdateStruct(UserGroupUpdateStruct $userGroupUpdateStruct)
    {
        $fields = array_flip($this->userGroupObject->data['fields']);
        array_walk($fields, array($userGroupUpdateStruct->contentUpdateStruct, 'setField'));
    }
}
