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
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\UserTestCase;

class UserTest extends UserTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testDelete()
    {
        $user = static::$repository->getUserService()->loadUserByLogin($this->_to_be_deleted_username);

        $userObject = new UserObject([]);
        $userObject->getMapper()->userToObject($user);
        $userObject->setProperty('action', Action::DELETE);

        $this->adapter->send(new Request(array(
            $userObject,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getUserService()->loadUserByLogin($this->_to_be_deleted_username);
    }
}
