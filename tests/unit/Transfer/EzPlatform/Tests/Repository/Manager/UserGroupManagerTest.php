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

        $this->assertInstanceOf(UserGroupObject::class, $usergroup);
        $this->assertGreaterThan(60, $usergroup->getProperty('id'));
    }

    public function testCreateEmpty()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->create(new ValueObject(array())));
    }

    public function testUpdate()
    {
        $manager = static::$userGroupManager;

        /** @var UserGroupObject $usergroup */
        $usergroup = $manager->create($this->getUserGroup());
        $id = $usergroup->getProperty('id');
        $this->assertEquals('My User Group', $usergroup->data['fields']['name']);
        $usergroup->data['fields']['name'] = 'My updated group';
        $usergroup = $manager->update($usergroup);
        $this->assertInstanceOf('Transfer\EzPlatform\Data\UserGroupObject', $usergroup);
        $this->assertEquals('My updated group', $usergroup->data['fields']['name']);
        $this->assertEquals($id, $usergroup->getProperty('id'));
    }

    public function testUpdateMoveParent()
    {
        $manager = static::$userGroupManager;

        /* @var UserGroupObject $usergroup */
        $newParentUsergroup = $manager->create($this->getUserGroup());
        $usergroup = $manager->create($this->getUserGroup());
        $this->assertEquals(12, $usergroup->data['parent_id']);
        $usergroup->data['parent_id'] = $newParentUsergroup->getProperty('id');
        $manager->update($usergroup);
        $this->assertEquals(83, $usergroup->data['parent_id']);
    }

    public function testUpdateEmpty()
    {
        $manager = static::$userGroupManager;
        $this->assertNull($manager->update(new ValueObject(array())));
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
        $found = $manager->find($usergroup);
        $this->assertFalse($found);
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
