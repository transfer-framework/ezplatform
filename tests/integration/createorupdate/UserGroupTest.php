<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\tests\testcase\UserGroupTestCase;

class UserGroupTest extends UserGroupTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testCreateAndUpdateUsergroup()
    {
        $rootUsergroup = $this->getRootUserGroup();

        $countOriginal = count(static::$repository->getUserService()->loadSubUserGroups($rootUsergroup));

        $remote_id = 'my_user_group_10';
        $name = 'Test Usergroup';

        // Will find by remote_id
        $raw = $this->getUsergroup(
            array('name' => $name),
            null,
            $remote_id
        );

        $this->adapter->send(new Request(array(
            $raw,
        )));

        $userGroups = static::$repository->getUserService()->loadSubUserGroups($rootUsergroup);
        $this->assertCount(($countOriginal + 1), $userGroups);

        $real = null;
        foreach ($userGroups as $userGroup) {
            if ($userGroup->getField('name')->value->text == $name) {
                $real = $userGroup;
                $raw->data['id'] = $userGroup->id;
                $raw->data['remote_id'] = $userGroup->contentInfo->remoteId;
                break;
            }
        }

        $this->assertInstanceOf(UserGroup::class, $real);
        $this->assertEquals('Test Usergroup', $real->contentInfo->name);

        $raw->data['fields']['name'] = 'My Updated Testgroup';
        $raw->data['parent_id'] = $rootUsergroup->id;

        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getUserService()->loadUserGroup($raw->data['id']);

        $this->assertInstanceOf(UserGroup::class, $real);
        $this->assertEquals('My Updated Testgroup', $real->getField('name')->value->text);

        // Checks that the usergroup has been moved.
        $this->assertEquals(12, $real->parentId);
    }

    public function testMoveUserGroup()
    {
        $remote_id = 'usergroup_moving';
        $users_members_node_id = 12;
        $users_administrators_node_id = 13;

        $userGroupObject = $this->getUsergroup(
            array('name' => 'This group is gonna move!'),
            $users_members_node_id,
            $remote_id
        );

        $response = $this->adapter->send(new Request(array(
            $userGroupObject,
        )));
        $userGroupObject = current($response->getData());
        $this->assertEquals($users_members_node_id, $userGroupObject->data['parent_id']);

        $userGroupObject->data['parent_id'] = $users_administrators_node_id;
        $response = $this->adapter->send(new Request(array(
            $userGroupObject,
        )));
        $userGroupObject = current($response->getData());
        $this->assertEquals($users_administrators_node_id, $userGroupObject->data['parent_id']);
    }
}
