<?php

namespace Transfer\EzPlatform\tests\integration;

use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class UserGroupTest extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
    }

    public function testCreateAndUpdateUsergroup()
    {
        $parentUsergroup = static::$repository->getUserService()->loadUserGroup(12);
        $countOriginal = count(static::$repository->getUserService()->loadSubUserGroups($parentUsergroup));

        $name = 'TestUsergroup';
        $raw = $this->getUsergroup($name);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $userGroups = static::$repository->getUserService()->loadSubUserGroups($parentUsergroup);
        $this->assertCount(($countOriginal + 1), $userGroups);

        $real = null;
        foreach ($userGroups as $userGroup) {
            if ($userGroup->getField('name')->value->text == $name) {
                $real = $userGroup;
                $raw->data['id'] = $userGroup->id;
                break;
            }
        }

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\User\UserGroup', $real);
        $this->assertEquals('TestUsergroup', $real->contentInfo->name);

        $raw->data['fields']['name'] = 'MyUpdatedTestgroup';
        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getUserService()->loadUserGroup($raw->data['id']);

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\User\UserGroup', $real);
        $this->assertEquals('MyUpdatedTestgroup', $real->getField('name')->value->text);

        $userGroups = static::$repository->getUserService()->loadSubUserGroups($parentUsergroup);
        $this->assertCount(($countOriginal + 1), $userGroups);
    }

    protected function getUsergroup($name)
    {
        return new UserGroupObject(array(
            'parent_id' => 12,
            'content_type_identifier' => 'user_group',
            'main_language_code' => 'eng-GB',
            'fields' => array(
                'name' => $name,
            ),
        ));
    }
}
