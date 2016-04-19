<?php

namespace Transfer\EzPlatform\tests\integration;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class LocationTest extends EzPlatformTestCase
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

    /**
     * Tests location creation.
     */
    public function testCreateLocation()
    {
        $locationObject = new LocationObject(array(
            'content_id' => 59,
            'parent_location_id' => 2,
            'remote_id' => '_test_location_integration_1',
        ));

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId('_test_location_integration_1');

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(59, $location->contentInfo->id);
    }

    public function testUpdateLocation()
    {
        $locationObject = new LocationObject(array(
            'content_id' => 59,
            'parent_location_id' => 58,
            'remote_id' => '_test_location_integration_1',
        ));

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId('_test_location_integration_1');

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals(59, $location->contentInfo->id);
    }

    public function testCreateContentAndLocation()
    {
        $locationObject = new LocationObject(array(
            'parent_location_id' => 58,
            'remote_id' => '_test_location_content_integration_2',
        ));
        $contentObject = new ContentObject(
            array(
                'title' => 'Test title',
            ),
            array(
                'language' => 'eng-GB',
                'content_type_identifier' => '_test_article',
                'remote_id' => '_test_content_location_integration_2',
                'parent_locations' => array(
                    $locationObject,
                    2,
                ),
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $newLocation1 = static::$repository->getLocationService()->loadLocationByRemoteId('_test_location_content_integration_2');
        $newContent = static::$repository->getContentService()->loadContentByRemoteId('_test_content_location_integration_2');

        $newLocations = static::$repository->getLocationService()->loadLocations($newContent->contentInfo);

        // Get the location which we did not give a remote Id
        $newLocation2 = null;
        foreach ($newLocations as $location) {
            if ($location->remoteId !== $newLocation1->remoteId) {
                $newLocation2 = $location;
                break;
            }
        }

        $this->assertInstanceOf(Location::class, $newLocation1);
        $this->assertInstanceOf(Location::class, $newLocation2);
        $this->assertInstanceOf(Content::class, $newContent);
        $this->assertEquals($newLocation1->contentId, $newContent->id);
        $this->assertEquals(58, $newLocation1->parentLocationId);
        $this->assertEquals(2, $newLocation2->parentLocationId);
    }
}
