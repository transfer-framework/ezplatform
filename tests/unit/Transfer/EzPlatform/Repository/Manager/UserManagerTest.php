<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\User\User;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

/**
 * User manager tests.
 */
class UserManagerTest extends EzPlatformTestCase
{
    public function testLogger()
    {
        $manager = static::$userManager;
        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $manager->setLogger($mockLogger);
    }

    public function testFind()
    {
        $manager = static::$userManager;

        /** @var UserObject $user */
        $user = $manager->createOrUpdate($this->getUser());
        /** @var User $result */
        $result = $manager->find($user);

        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\User\User', $result);
        $this->assertEquals($user->data['username'], $result->login);
    }

    public function testCreate()
    {
        $manager = static::$userManager;
        $user = $this->getUser();
        $user->data['username'] = 'new_user';
        /** @var UserObject $newUser */
        $newUser = $manager->create($user);
        $this->assertGreaterThan(1, $newUser->data['id']);
        $this->assertEquals($user->getData(), $newUser->getData());
    }

    public function testCreateEmpty()
    {
        $manager = static::$userManager;
        $this->assertNull($manager->create(new ValueObject(array())));
    }

    public function testUpdate()
    {
        $manager = static::$userManager;

        $manager->update($this->getUpdateUser());

        /** @var User $updatedUser */
        $updatedUser = $manager->find($this->getUpdateUser());
        $this->assertEquals('updated@example.com', $updatedUser->email);
        $this->assertEquals('da9287bf067f474372b58fd3d8470da9', $updatedUser->passwordHash);
        $this->assertEquals('Updated', $updatedUser->getField('first_name')->value);
        $this->assertEquals('Updatesen', $updatedUser->getField('last_name')->value);
    }

    public function testUpdateInvalid()
    {
        $manager = static::$userManager;
        $null = $manager->update(new ValueObject(array()));
        $this->assertNull($null);
    }

    public function testCreate_CreateOrUpdate()
    {
        $manager = static::$userManager;
        $user = $this->getUser();
        $user->data['username'] = 'create_createOrUpdate';
        $user = $manager->createOrUpdate($user);
        $this->assertInstanceOf(UserObject::class, $user);
    }

    public function testUpdate_CreateOrUpdate()
    {
        $manager = static::$userManager;
        $user = $this->getUser();
        $user = $manager->createOrUpdate($user);
        $this->assertInstanceOf(UserObject::class, $user);
    }

    public function testCreateOrUpdateInvalid()
    {
        $manager = static::$userManager;
        $null = $manager->createOrUpdate(new ValueObject(array()));
        $this->assertNull($null);
    }

    public function testRemove()
    {
        $manager = static::$userManager;
        $this->assertTrue($manager->remove($this->getUser()));
    }

    public function testRemoveInvalid()
    {
        $manager = static::$userManager;
        $null = $manager->remove(new ValueObject(array()));
        $this->assertNull($null);
    }

    public function testRemoveNotFound()
    {
        $manager = static::$userManager;
        $user = $this->getUser();
        $user->data['username'] = 'some_random_username';
        $this->assertTrue($manager->remove($user));
    }

    protected function getUser()
    {
        return new UserObject(array(
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'test123',
            'main_language_code' => 'eng-GB',
            'enabled' => true,
            'fields' => array(
                'first_name' => 'Test',
                'last_name' => 'User',
            ),
            'parents' => array(
                new UserGroupObject(array(
                    'parent_id' => 12,
                    'content_type_identifier' => 'user_group',
                    'main_language_code' => 'eng-GB',
                    'fields' => array(
                        'name' => 'My User Group',
                    ),
                )),
            ),
        ));
    }

    protected function getUpdateUser()
    {
        $user = $this->getUser();
        $user->data['email'] = 'updated@example.com';
        $user->data['password'] = 'test456';
        $user->data['fields'] = array(
            'first_name' => 'Updated',
            'last_name' => 'Updatesen',
        );

        return $user;
    }
}
