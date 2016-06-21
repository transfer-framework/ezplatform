<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration;

use eZ\Publish\API\Repository\Values\User\UserGroup;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\LanguageObject;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class EzPlatformAdapterTest extends EzPlatformTestCase
{
    /**
     * Testing adapter options for main_language_code.
     *
     *  Note that we are passing the value to the adapter on
     *  creation, not passing it in a ContentObject with the Request.
     */
    public function testUseCustomMainLanguageCode()
    {
        $content_remote_id = 'adapter_test_content_1';
        $custom_language = 'nor-NO';
        $user_email = 'harald@languagetest.com';

        // Dont use setup, as we wont to set our own options
        $adapter = new EzPlatformAdapter(static::$repository, array(
            'main_language_code' => $custom_language,
        ));

        $adapter->send(new Request(array(
            new LanguageObject(['code' => $custom_language]),   // LanguageObject
            $this->getContentObjectExample($content_remote_id), // ContentObject & LocationObject
            $this->getUserObjectExample($user_email),           // UserObject & UserGroupObject
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($content_remote_id);
        $this->assertEquals($content->contentInfo->mainLanguageCode, $custom_language);
        $this->assertEquals($content->getField('name')->languageCode, $custom_language);

        $user = static::$repository->getUserService()->loadUserByLogin($user_email);
        $this->assertEquals($user->contentInfo->mainLanguageCode, $custom_language);
        $this->assertEquals($user->getField('first_name')->languageCode, $custom_language);

        $userGroups = static::$repository->getUserService()->loadUserGroupsOfUser($user);
        /** @var UserGroup $userGroups */
        $userGroups = current($userGroups);
        $this->assertEquals($userGroups->contentInfo->mainLanguageCode, $custom_language);
        $this->assertEquals($userGroups->getField('name')->languageCode, $custom_language);
    }

    private function getContentObjectExample($remote_id)
    {
        return new ContentObject(
            array('name' => 'Mitt produkt'),
            array(
                'remote_id' => $remote_id,
                'content_type_identifier' => 'product',
                'parent_locations' => array(2),
            )
        );
    }

    private function getUserObjectExample($email)
    {
        return new UserObject(array(
            'username' => $email,
            'email' => $email,
            'password' => 'test123',
            'fields' => array(
                'first_name' => 'Test',
                'last_name' => 'User',
            ),
            'parents' => array(
                new UserGroupObject(array(
                    'parent_id' => 12,
                    'content_type_identifier' => 'user_group',
                    'fields' => array(
                        'name' => 'Min brukergruppe',
                    ),
                )),
            ),
        ));
    }
}
