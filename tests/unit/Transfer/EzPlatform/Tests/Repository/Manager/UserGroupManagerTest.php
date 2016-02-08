<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * User manager tests.
 */
class UserGroupManagerTest extends EzPlatformTestCase
{
    public function testLogger()
    {
        $manager = static::$userGroupManager;
        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $manager->setLogger($mockLogger);
    }

    public function testCreate()
    {
        $manager = static::$userGroupManager;

        /** @var UserGroupObject $usergroup */
        $usergroup = $manager->create($this->getUserGroup());

        $this->assertInstanceOf('Transfer\EzPlatform\Data\UserGroupObject', $usergroup);
        $this->assertGreaterThan(60, $usergroup->data['id']);
    }

    public function testCreateEmpty()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->create(new ValueObject(array())));
    }

    public function testCreateUnknownParent()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UserGroupNotFoundException');
        $manager = static::$userGroupManager;

        $usergroup = $this->getUserGroup();
        $usergroup->data['parent_id'] = 45678;

        $manager->create($usergroup);
    }

    public function testUpdate()
    {
        $manager = static::$userGroupManager;

        /** @var UserGroupObject $usergroup */
        $usergroup = $manager->create($this->getUserGroup());
        $id = $usergroup->data['id'];
        $this->assertEquals('My User Group', $usergroup->data['fields']['name']);
        $usergroup->data['fields']['name'] = 'My updated group';
        $usergroup = $manager->update($usergroup);
        $this->assertInstanceOf('Transfer\EzPlatform\Data\UserGroupObject', $usergroup);
        $this->assertEquals('My updated group', $usergroup->data['fields']['name']);
        $this->assertEquals($id, $usergroup->data['id']);
    }

    public function testUpdateMoveParent()
    {
        $manager = static::$userGroupManager;

        /* @var UserGroupObject $usergroup */
        $newParentUsergroup = $manager->create($this->getUserGroup());
        $usergroup = $manager->create($this->getUserGroup());
        $this->assertEquals(12, $usergroup->data['parent_id']);
        $usergroup->data['parent_id'] = $newParentUsergroup->data['id'];
        $manager->update($usergroup);
        $this->assertEquals(79, $usergroup->data['parent_id']);
    }

    public function testUpdateEmpty()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->update(new ValueObject(array())));
    }

    public function testUpdateUnknown()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UserGroupNotFoundException');
        $manager = static::$userGroupManager;

        $manager->update($this->getUserGroup());
    }

    public function testUpdateNotFound()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UserGroupNotFoundException');
        $manager = static::$userGroupManager;

        $usergroup = $this->getUserGroup();
        $usergroup->data['id'] = PHP_INT_MAX;

        $manager->update($usergroup);
    }

    public function testCreateOrUpdate()
    {
        $manager = static::$userGroupManager;

        /** @var UserGroupObject $usergroup */
        $usergroup = $manager->createOrUpdate($this->getUserGroup());
        $usergroup = $manager->createOrUpdate($usergroup);
        $this->assertInstanceOf('Transfer\EzPlatform\Data\UserGroupObject', $usergroup);
    }

    public function testCreateOrUpdateEmpty()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->createOrUpdate(new ValueObject(array())));
    }

    public function testRemove()
    {
        $manager = static::$userGroupManager;

        /** @var UserGroupObject $usergroup */
        $usergroup = $manager->createOrUpdate($this->getUserGroup());
        $deleted = $manager->remove($usergroup);
        $this->assertTrue($deleted);
        $found = $manager->find($usergroup->data['id']);
        $this->assertFalse($found);
    }

    public function testRemoveNotSetId()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UserGroupNotFoundException');

        $manager = static::$userGroupManager;
        $userGroup = $this->getUserGroup();
        $manager->remove($userGroup);
    }

    public function testRemoveNotFound()
    {
        $manager = static::$userGroupManager;

        $usergroup = $this->getUserGroup();
        $usergroup->data['id'] = PHP_INT_MAX;

        $this->setExpectedException(
            'Transfer\EzPlatform\Exception\UserGroupNotFoundException',
            'Usergroup with id "'.PHP_INT_MAX.'" not found.'
        );

        $manager->remove($usergroup);
    }

    public function testRemoveInvalid()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->remove(new ValueObject(array())));
    }

    protected function getUserGroup()
    {
        return new UserGroupObject(array(
            'parent_id' => 12,
            'content_type_identifier' => 'user_group',
            'main_language_code' => 'eng-GB',
            'fields' => array(
                'name' => 'My User Group',
            ),
        ));
    }
}
