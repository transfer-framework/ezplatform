<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\UserGroupObject;
use Transfer\EzPlatform\Data\UserObject;
use Transfer\EzPlatform\tests\EzPlatformTestCase;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class EzPlatformAdapterTest extends EzPlatformTestCase
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

    public function testSendContentObject()
    {
        $contentObject = new ContentObject(
            array(
                'title' => 'Test',
            ),
            array(
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => 'test_1',
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));
    }

    public function testSendTreeObject()
    {
        $contentObject = new ContentObject(
            array(
                'title' => 'Test',
            ),
            array(
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => 'test_2',
            )
        );

        $treeObject = new TreeObject($contentObject);
        $treeObject->setProperty('parent_location_id', 2);

        $this->adapter->send(new Request(array(
            $treeObject,
        )));
    }

    public function testSendFullContentTypeObject()
    {
        $ct = new ContentTypeObject(array(
            'identifier' => '_test_article',
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'names' => array('eng-GB' => 'Article'),
            'descriptions' => array('eng-GB' => 'Article description'),
            'is_container' => true,
            'default_always_available' => false,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'name' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Name'),
                    'descriptions' => array('eng-GB' => 'Name of the article'),
                    'field_group' => 'content',
                    'position' => 10,
                    'is_required' => true,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
                'description' => array(
                    'type' => 'ezrichtext',
                    'names' => array('eng-GB' => 'Description'),
                    'descriptions' => array('eng-GB' => 'Description of the article'),
                    'field_group' => 'content',
                    'position' => 20,
                    'is_required' => false,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,

                ),
            ),
        ));

        $this->adapter->send(new Request(array(
            $ct,
        )));
    }

    public function testSendMiniContentTypeObject()
    {
        $array = array(array(
            'identifier' => 'article',
            'fields' => array(
                'title' => array(),
                'content' => array(),
            ),
        ));

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);
        $ct = $ct[0];

        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $this->adapter->setLogger($mockLogger);

        $this->adapter->send(new Request(array(
            $ct,
        )));
    }

    public function testSendUserObject()
    {
        $user = new UserObject(array(
            'username' => 'test_user',
            'email' => 'test@example.com',
            'password' => 'test123',
            'main_language_code' => 'eng-GB',
            'enabled' => true,
            'fields' => array(
                'first_name' => 'Test',
                'last_name' => 'User',
            ),
        ));
        $user->parents = array(
            new UserGroupObject(array(
                'fields' => array(
                    'name' => 'Members',
                ),
            )),
        );

        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $this->adapter->setLogger($mockLogger);

        $this->adapter->send(new Request(array(
            $user,
        )));
    }

    public function testSendUserGroup()
    {
        $userGroup = new UserGroupObject(array(
            'fields' => array(
                'name' => 'Members',
            ),
        ));

        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $this->adapter->setLogger($mockLogger);

        $response = $this->adapter->send(new Request(array(
            $userGroup,
        )));

        $this->assertInstanceOf('Transfer\Adapter\Transaction\Response', $response);
        $this->assertCount(1, $response);
        $object = iterator_to_array($response);
        $this->assertInstanceOf('Transfer\Ezplatform\Data\UserGroupObject', $object[0]);
    }
}
