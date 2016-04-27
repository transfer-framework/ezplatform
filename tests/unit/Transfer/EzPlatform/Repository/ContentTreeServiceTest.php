<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use Psr\Log\NullLogger;
use Transfer\Data\TreeObject;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\ContentTreeService;
use Transfer\EzPlatform\Repository\ObjectService;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ContentTreeServiceTest extends EzPlatformTestCase
{
    /** @var ContentTreeService $service */
    private $service;

    public function setUp()
    {
        $objectService = new ObjectService(static::$repository);
        $objectService->setLogger(new NullLogger());
        $this->service = new ContentTreeService(static::$repository, $objectService);
        $this->service->setLogger(new NullLogger());
    }

    public function testCreate()
    {
        $rootContentObject = new ContentObject(
            array(
                'title' => 'Test',
            ),
            array(
                'content_type_identifier' => ContentTestCase::_content_type_article,
                'language' => 'eng-GB',
                'remote_id' => 'content_tree_service_test_1',
                'priority' => 1,
            )
        );

        $secondaryContentObject = new ContentObject(
            array(
                'title' => 'Test 2',
            ),
            array(
                'content_type_identifier' => ContentTestCase::_content_type_article,
                'language' => 'eng-GB',
                'remote_id' => 'content_tree_service_test_2',
                'priority' => 1,
                'hidden' => true,
                'main_object' => false,
            )
        );

        $treeObject = new TreeObject($rootContentObject);
        $treeObject->setProperty('parent_location_id', 2);

        $treeObject->addNode($secondaryContentObject);
        $treeObject->addNode(new TreeObject($secondaryContentObject));

        $this->service->createOrUpdate($treeObject);
    }

    public function testCreateWithInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->service->createOrUpdate(new ValueObject(null));
    }
}
