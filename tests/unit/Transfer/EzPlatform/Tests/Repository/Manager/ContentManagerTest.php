<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use Psr\Log\LoggerInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
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

    public function testCreateWithLocation()
    {
        $contentObject = new ContentObject(
            array( // fields
                'name' => 'Test name',
                'title' => 'Test title',
                'description' => 'Test description',
            ), array( // properties
                'parent_locations' => array(2),
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => '_test_locations_1',
            )
        );

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->findByRemoteId('_test_locations_1');

        $this->assertEquals('Test name', (string) $createdContentObject->data['name']['eng-GB']);
        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);

        $parentLocations = $createdContentObject->getParentLocations();

        $this->assertCount(1, $parentLocations);
        $this->assertInstanceOf(LocationCreateStruct::class, current($parentLocations));
        $this->assertEquals(2, current($parentLocations)->parentLocationId);
    }

    public function testCreateWithMulipleTypesOfLocation()
    {
        $contentObject = new ContentObject(array(
            'name' => 'Test title',
            'title' => 'Test title',
            'description' => 'Test description',
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('_test_locations_2');
        $contentObject->addParentLocation(2);
        $contentObject->addParentLocation(new LocationObject(array('parent_id' => 2)));
        $locationStruct = new LocationCreateStruct();
        $locationStruct->parentLocationId = 2;
        $contentObject->addParentLocation($locationStruct);

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->findByRemoteId('_test_locations_2');

        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);
    }

    public function testCreateWithInvalidLocation()
    {
        $this->setExpectedException(InvalidDataStructureException::class);

        $this->manager->create(
            new ContentObject(
                array(
                    'name' => 'Test name',
                    'title' => 'Test title',
                    'description' => 'Test description',
                ), array(
                    'parent_locations' => array('im not a valid location'),
                    'content_type_identifier' => '_test_article',
                    'language' => 'eng-GB',
                    'remote_id' => '_test_locations_1',
                )
            )
        );
    }

    public function testUpdateithMulipleTypesOfLocation()
    {
        $contentObject = new ContentObject(array(
            'name' => 'Test title',
            'title' => 'Test title',
            'description' => 'Test description',
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('_test_locations_2');
        $contentObject->addParentLocation(2);
        $contentObject->addParentLocation(new LocationObject(array('parent_id' => 5)));
        $locationStruct = new LocationCreateStruct();
        $locationStruct->parentLocationId = 5;
        $contentObject->addParentLocation($locationStruct);

        $this->manager->update($contentObject);

        $createdContentObject = $this->manager->findByRemoteId('_test_locations_2');

        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);

        $parentLocations = $createdContentObject->getParentLocations();

        $this->assertCount(1, $parentLocations);
        $this->assertInstanceOf(LocationCreateStruct::class, current($parentLocations));
        $this->assertEquals(5, current($parentLocations)->parentLocationId);
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
