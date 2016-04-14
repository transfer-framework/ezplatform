<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use Psr\Log\NullLogger;
use Transfer\Data\TreeObject;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\ContentTreeService;
use Transfer\EzPlatform\Repository\ObjectService;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

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
                'name' => 'Test',
            ),
            array(
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => 'content_tree_service_test_1',
                'priority' => 1,
            )
        );

        $secondaryContentObject = new ContentObject(
            array(
                'title' => 'Test 2',
                'name' => 'Test 2',
            ),
            array(
                'content_type_identifier' => '_test_article',
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

        $this->service->create($treeObject);
    }

    public function testCreateWithInvalidArgument()
    {
        $this->setExpectedException('InvalidArgumentException');

        $this->service->create(new ValueObject(null));
    }
}
