<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use Psr\Log\LoggerInterface;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Exception\MissingIdentificationPropertyException;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\tests\EzPlatformTestCase;

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
        $this->manager = static::$contentManager;

        /** @var LoggerInterface $logger */
        $logger = $this->getMock('Psr\Log\LoggerInterface');
        $this->manager->setLogger($logger);
    }

    public function testCreate()
    {
        $contentObject = new ContentObject(
            array( // fields
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ), array( // properties
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => '_test_1',
            )
        );

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->find($contentObject);

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
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => '_test_locations_1',
            )
        );
        $contentObject->addParentLocation(2);

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->find($contentObject);

        $this->assertEquals('Test name', (string) $createdContentObject->data['name']['eng-GB']);
        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);

        $parentLocations = $createdContentObject->getProperty('parent_locations');

        $this->assertCount(1, $parentLocations);
        $this->assertInstanceOf(LocationObject::class, current($parentLocations));
        $this->assertEquals(2, current($parentLocations)->data['parent_location_id']);
    }

    public function testCreateWithMulipleTypesOfLocation()
    {
        $contentObject = new ContentObject(
            array( // fields
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ), array( // properties
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => '_test_locations_2',
            )
        );

        $contentObject->addParentLocation(2);
        $contentObject->addParentLocation(new LocationObject(array(
            'parent_location_id' => 2,
            'remote_id' => 'content_location_1',
        )));

        $this->manager->create($contentObject);

        $createdContentObject = $this->manager->find($contentObject);

        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);
    }

/* @todo remove comments
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
*/
    public function testUpdateithMulipleTypesOfLocation()
    {
        $contentObject = new ContentObject(
            array(
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ), array(
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => '_test_locations_1',
            )
        );
/*
        $location = static::$repository->getLocationService()->loadLocation(2);
        print_r(static::$repository->getLocationService()->loadLocationChildren($location));
*/
        $contentObject->addParentLocation(58);
        $contentObject->addParentLocation(new LocationObject(array(
            'parent_location_id' => 62,
            'remote_id' => 'content_location_62',
        )));

        $this->manager->createOrUpdate($contentObject);

        $updatedContentObject = $this->manager->find($contentObject);

        $this->assertEquals('Test title', $updatedContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', $updatedContentObject->data['description']['eng-GB']);

        $parentLocations = $updatedContentObject->getProperty('parent_locations');
        $this->assertCount(2, $parentLocations);
        $this->assertInstanceOf(LocationObject::class, current($parentLocations));
        $this->assertEquals(58, current($parentLocations)->data['parent_location_id']);
    }

    /*
     public function testUpdateContentWithoutLocations
     */

    public function testCreateWithInvalidArgument()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $this->manager->create(new ValueObject(null));
    }

    public function testUpdate()
    {
        $object = $this->manager->find(new ContentObject([], ['remote_id' => '_test_1']));

        $this->assertNotNull($this->manager->update($object));
    }

    public function testUpdateWithInvalidArgument()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $this->manager->update(new ValueObject(null));
    }

    public function testCreateOrUpdateWithExistingObject()
    {
        $object = $this->manager->find(new ContentObject([], ['remote_id' => '_test_1']));

        $this->assertNotNull($this->manager->createOrUpdate($object));
    }

    public function testCreateOrUpdateWithNonExistingObject()
    {
        $object = new ContentObject(
            array(
                'name' => 'Test title',
                'title' => 'Test title',
                'description' => 'Test description',
            ), array(
                'content_type_identifier' => '_test_article',
                'language' => 'eng-GB',
                'remote_id' => 1,
            )
        );

        $this->assertNotNull($this->manager->createOrUpdate($object));
    }

    public function testCreateOrUpdateWithAmbiguousObject()
    {
        $this->setExpectedException(MissingIdentificationPropertyException::class);

        $object = new ContentObject(array());

        $this->manager->createOrUpdate($object);
    }

    public function testCreateOrUpdateWithInvalidArgument()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $this->manager->createOrUpdate(new ValueObject(null));
    }

    public function testRemove()
    {
        $object = $this->manager->find(new ContentObject([], ['remote_id' => '_test_1']));

        $this->assertTrue($this->manager->remove($object));
    }

    public function testRemoveWithInvalidArgument()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $this->manager->remove(new ValueObject(null));
    }

    public function testRemoveWithNonExistingObject()
    {
        $this->assertFalse($this->manager->remove(new ContentObject(array())));
    }
}
