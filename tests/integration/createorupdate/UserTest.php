<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\User\User;
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
}
