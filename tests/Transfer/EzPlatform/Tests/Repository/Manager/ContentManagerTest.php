<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Psr\Log\LoggerInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Content manager tests.
 */
class ContentManagerTest extends EzPlatformTestCase
{
    /**
     * @var ContentManager
     */
    private $manager;

    public function setUp()
    {
        $this->manager = new ContentManager(static::$repository);

        /** @var LoggerInterface $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->manager->setLogger($logger);
    }

    public function testCreate()
    {
        $contentObject = new ContentObject(array(
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('_test_1');

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->findByRemoteId('_test_1');

        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);
    }

    public function testCreateWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->manager->create(new ValueObject(null));
    }

    public function testUpdate()
    {
        $object = $this->manager->findByRemoteId('_test_1');

        $this->assertNotNull($this->manager->update($object));
    }

    public function testUpdateWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->manager->update(new ValueObject(null));
    }

    public function testCreateOrUpdateWithExistingObject()
    {
        $object = $this->manager->findByRemoteId('_test_1');
        $object->setProperty('remote_id', '_test_1');

        $this->assertNotNull($this->manager->createOrUpdate($object));
    }

    public function testCreateOrUpdateWithNonExistingObject()
    {
        $object = new ContentObject(array(
            'name' => 'Test title',
            'title' => 'Test title',
            'description' => 'Test description',
        ));

        $object->setProperty('remote_id', 1);
        $object->setContentType('_test_article');
        $object->setLanguage('eng-GB');

        $this->assertNotNull($this->manager->createOrUpdate($object));
    }

    public function testCreateOrUpdateWithAmbiguousObject()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\MissingIdentificationPropertyException');

        $object = new ContentObject(array());

        $this->manager->createOrUpdate($object);
    }

    public function testCreateOrUpdateWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->manager->createOrUpdate(new ValueObject(null));
    }

    public function testRemove()
    {
        $object = $this->manager->findByRemoteId('_test_1');
        $object->setRemoteId('_test_1');

        $this->assertTrue($this->manager->remove($object));
    }

    public function testRemoveWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->manager->remove(new ValueObject(null));
    }

    public function testRemoveWithNonExistingObject()
    {
        $this->assertFalse($this->manager->remove(new ContentObject(array())));
    }
}
