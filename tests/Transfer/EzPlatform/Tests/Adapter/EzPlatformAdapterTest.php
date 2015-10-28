<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class EzPlatformAdapterTests extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository
        ));
    }

    public function testSendContentObject()
    {
        $contentObject = new ContentObject(array(
            'title' => 'Test'
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('test_1');

        $this->adapter->send(new Request(array(
            $contentObject
        )));
    }

    public function testSendTreeObject()
    {
        $contentObject = new ContentObject(array(
            'title' => 'Test'
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('test_2');

        $treeObject = new TreeObject($contentObject);
        $treeObject->setProperty('location_id', 2);

        $this->adapter->send(new Request(array(
            $treeObject
        )));
    }
}
