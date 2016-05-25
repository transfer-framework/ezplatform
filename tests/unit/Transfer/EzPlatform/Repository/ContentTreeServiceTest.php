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
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Manager\Core\ContentTreeService;
use Transfer\EzPlatform\Repository\Manager\Core\ObjectService;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\ContentTreeTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ContentTreeServiceTest extends ContentTreeTestCase
{
    public function setUp()
    {
        parent::setUp();
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

        static::$contentTreeService->createOrUpdate($treeObject);
    }

    public function testCreateWithInvalidArgument()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        static::$contentTreeService->createOrUpdate(new ValueObject(null));
    }
}
