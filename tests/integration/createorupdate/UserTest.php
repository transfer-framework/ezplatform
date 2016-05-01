<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class UserTest extends EzPlatformTestCase
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
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }

    public function testCreateAndUpdateUser()
    {
        $username = 'user@example.com';
        $raw = $this->getUser($username);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getUserService()->loadUserByLogin($username);
        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\User\User', $real);
        $this->assertEquals('user@example.com', $real->email);

        $raw = $this->getUser($username);
        $raw->data['email'] = 'something@example.com';
        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getUserService()->loadUserByLogin($username);
        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\User\User', $real);
        $this->assertEquals('something@example.com', $real->email);
    }

    protected function getUser($username)
    {
        return new UserObject(array(
            'username' => $username,
            'email' => $username,
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
}
