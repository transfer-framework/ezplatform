<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\User\User;
use eZ\Publish\API\Repository\Values\User\UserCreateStruct;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\tests\testcase\UserTestCase;

class UserTest extends UserTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Creates a user, and later updates his email.
     */
    public function testCreateAndUpdateUser()
    {
        $username = 'user@example.com';

        $currentEmail = $username;
        $newEmail = 'something@example.com';

        $raw = $this->getUser($username, $currentEmail);

        // Create
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getUserService()->loadUserByLogin($username);
        $this->assertInstanceOf(User::class, $real);
        $this->assertEquals($currentEmail, $real->email);

        // Update
        $raw->data['email'] = $newEmail;
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getUserService()->loadUserByLogin($username);
        $this->assertInstanceOf(User::class, $real);
        $this->assertEquals($newEmail, $real->email);
    }

    /**
     * Tests user struct callback.
     */
    public function testStructCallback()
    {
        $username = 'structcallback@example.com';
        $sectionId = 10;

        $userObject = $this->getUser($username);

        $userObject->setStructCallback(function (UserCreateStruct $struct) use ($sectionId) {
            $struct->sectionId = $sectionId;
        });

        $this->adapter->send(new Request(array(
            $userObject,
        )));

        $user = static::$repository->getUserService()->loadUserByLogin($username);

        $this->assertEquals($sectionId, $user->contentInfo->sectionId);
    }
}
