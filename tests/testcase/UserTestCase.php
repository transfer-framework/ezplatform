<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\testcase;

use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;

class UserTestCase extends EzPlatformTestCase
{
    protected $_to_be_deleted_username = 'toBeDeleted@example.com';

    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        parent::setUp();
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );

        $this->setUpUsers();
    }

    protected function setUpUsers()
    {
        $usernames = [
            $this->_to_be_deleted_username,
        ];

        $userObjects = [];
        foreach ($usernames as $username) {
            $userObjects[] = $this->getUser($username);
        }

        foreach ($userObjects as $userObject) {
            static::$userManager->createOrUpdate($userObject);
        }
    }

    protected function getUser($username, $email = null)
    {
        $email = $email ?: $username;

        return new UserObject(array(
            'username' => $username,
            'email' => $email,
            'password' => 'test123',
            'main_language_code' => 'eng-GB',
            'max_login' => 1000,
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
}
