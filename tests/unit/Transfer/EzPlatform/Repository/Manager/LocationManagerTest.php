<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\LocationObject;
use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class LocationManagerTest extends EzPlatformTestCase
{
    /**
     * @var LocationManager
     */
    private $locM;

    /**
     * @var LocationManager
     */
    private $conM;

    public function setUp()
    {
        $this->locM = static::$locationManager;
        $this->conM = static::$contentManager;

        $this->setLoggers();

        $contentObject = new ContentObject(
            array( // fields
                'title' => 'Test title',
                'description' => 'Test description',
            ), array( // properties
                'content_type_identifier' => ContentTestCase::_content_type_article,
                'language' => 'eng-GB',
                'remote_id' => '_test_content_location',
                'parent_locations' => array(
                    new LocationObject(array(
                        'remote_id' => '_test_location_content',
                        'parent_location_id' => 58,
                    )),
                ),
            )
        );

        static::$contentManager->createOrUpdate($contentObject);
    }

    public function testCreate()
    {
        $locationObject = new LocationObject(array(
            'content_id' => 80,
            'parent_location_id' => 2,
            'remote_id' => '_test_location_1',
        ));


        $location = $this->locM->create($locationObject);

        $this->assertInstanceOf(LocationObject::class, $location);
    }

    public function testLoadContentAndCreateLocationWithoutContentId()
    {
        $content = static::$contentManager->find(new ContentObject(array(), array('id' => 79)));
        $content->addParentLocation(new LocationObject(array(
            'parent_location_id' => 2,
        )));

        $this->assertEquals($content->getProperty('id'), $content->getProperty('parent_locations')[2]->data['content_id']);
    }

    public function testCreateWrongObject()
    {
        $this->assertNull($this->locM->create(new ValueObject(array())));
    }

    public function testCreateContentWithLocationWithInvalidParentId()
    {
        $this->setExpectedException(InvalidDataStructureException::class);

        new ContentObject(
            array(),
            array(
                'parent_locations' => array(
                    new LocationObject(array(
                        'parent_location_id' => 0,
                    )),
                ),
            )
        );
    }

    public function testUpdate()
    {
        $locationObject = new LocationObject(array(
            'content_id' => 78,
            'parent_location_id' => 58,
            'remote_id' => '_test_location_1',
        ));

        $location = $this->locM->createOrUpdate($locationObject);
        $this->assertInstanceOf(LocationObject::class, $location);
    }

    public function testUpdateWithRemoteId()
    {
        $targetParentLocationId = 62;

        $location1 = static::$repository->getLocationService()->loadLocation(2);
        $locations1 = static::$repository->getLocationService()->loadLocationChildren($location1);

        $location = false;
        foreach ($locations1->locations as $locationX) {
            $locations = static::$repository->getLocationService()->loadLocationChildren($locationX);
            if ($locations->totalCount > 0) {
                foreach ($locations->locations as $locationY) {
                    if ($locationY->parentLocationId != $targetParentLocationId) {
                        $location = $locationY;
                        break 2;
                    }
                }
            }
        }

        $locationObject = new LocationObject(array(
            'parent_location_id' => $targetParentLocationId,
            'content_id' => $location->contentId,
        ));

        $locationObject = $this->locM->createOrUpdate($locationObject);
        $locationObject = $this->locM->createOrUpdate($locationObject);
        $this->assertInstanceOf(LocationObject::class, $locationObject);
    }

    public function testUpdateNotLocationObject()
    {
        $this->assertNull($this->locM->update(new ValueObject(array())));
    }

    public function testRemove()
    {
        $this->assertNull(
            $this->locM->remove(new ValueObject(array()))
        );
    }

    public function testHide()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $this->assertInstanceOf(
            Location::class,
            $this->locM->hide($location)
        );
    }

    public function testUnHide()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $this->assertInstanceOf(
            Location::class,
            $this->locM->unHide($location)
        );
    }

    public function testToggleVisibility()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);

        $this->assertInstanceOf(
            Location::class,
            $this->locM->toggleVisibility($location)
        );

        $this->assertInstanceOf(
            Location::class,
            $this->locM->toggleVisibility($location)
        );
    }

    public function testFindById()
    {
        $originalLocation = static::$repository->getLocationService()->loadLocation(2);
        $object = new LocationObject($originalLocation);

        $location = $this->locM->find($object);
        $this->assertEquals($originalLocation->id, $location->id);
    }
}
