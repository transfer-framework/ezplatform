<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\tests\testcase\UserGroupTestCase;

class UserGroupTest extends UserGroupTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testDelete()
    {
        $userGroup = static::$repository->getUserService()->loadUserGroup($this->_to_be_deleted_usergroup_id);

        $userGroupObject = new UserGroupObject([]);
        $userGroupObject->getMapper()->userGroupToObject($userGroup);
        $userGroupObject->setProperty('action', Action::DELETE);

        $this->adapter->send(new Request(array(
            $userGroupObject,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getUserService()->loadUserGroup($this->_to_be_deleted_usergroup_id);
    }
}
