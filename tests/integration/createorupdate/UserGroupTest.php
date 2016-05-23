<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Transfer\Adapter\Transaction\Request;
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

        $name = 'Test Usergroup';

        $raw = $this->getUsergroup(array('name' => $name));
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
                break;
            }
        }

        $this->assertInstanceOf(UserGroup::class, $real);
        $this->assertEquals('Test Usergroup', $real->contentInfo->name);

        $raw->data['fields']['name'] = 'My Updated Testgroup';
        $raw->data['parent_id'] = 14;

        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getUserService()->loadUserGroup($raw->data['id']);

        $this->assertInstanceOf(UserGroup::class, $real);
        $this->assertEquals('My Updated Testgroup', $real->getField('name')->value->text);

        // Checks that the usergroup has been moved.
        $this->assertEquals(14, $real->parentId);
    }
}
