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
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
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

    /**
     * Creates a new Content Type, with an ezuser fieldtype.
     * Then we create a new user, with this custom user contenttype.
     */
    public function testCreateWithCustomContentType()
    {
        $contentTypeIdentifier = 'new_user_contenttype';
        $contentTypeName = 'My New User Contenttype';

        $contentObjectData = new ContentTypeObject(array(
            'identifier' => $contentTypeIdentifier,
            'names' => array('eng-GB' => $contentTypeName),
            'fields' => array(
                'identification' => array(
                    'type' => 'ezinteger',
                    'position' => 5,
                ),
                'full_name' => array(
                    'type' => 'ezstring',
                    'position' => 10,
                ),
                'account' => array(
                    'type' => 'ezuser',
                    'position' => 20,
                ),
            ),
        ));

        $username = 'contenttypeuser@example.com';
        $userObject = new UserObject(array(
            'username' => $username,
            'email' => $username,
            'password' => 'test123',
            'main_language_code' => 'eng-GB',
            'content_type' => $contentTypeIdentifier,
            'fields' => array(
                'identification' => 1337,
                'full_name' => 'Harald Tollefsen',
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

        $this->adapter->send(new Request(array(
            $contentObjectData,
            $userObject,
        )));

        $user = static::$repository->getUserService()->loadUserByLogin($username);
        $this->assertEquals($username, $user->getField('account')->value->login);

        $contentType = static::$repository->getContentTypeService()->loadContentType($user->contentInfo->contentTypeId);
        $this->assertEquals($contentTypeName, $contentType->getName('eng-GB'));
    }
}
