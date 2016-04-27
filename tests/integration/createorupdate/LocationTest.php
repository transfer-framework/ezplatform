<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\LocationTestCase;

class LocationTest extends LocationTestCase
{

    /**
     * Tests location creation.
     */
    public function testCreateLocation()
    {
        $remoteId = '_test_location_integration_1';
        $parentLocationId = 60;

        $locationObject = $this->getLocationObject($remoteId, $this->_test_contentId_1, $parentLocationId);

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId($remoteId);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($this->_test_contentId_1, $location->contentInfo->id);
        $this->assertEquals($parentLocationId, $location->parentLocationId);
    }

    public function testUpdateLocation()
    {
        $remoteId = '_test_location_integration_1';
        $parentLocationId = 64;

        $locationObject = $this->getLocationObject($remoteId, $this->_test_contentId_1, $parentLocationId);

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId($remoteId);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($this->_test_contentId_1, $location->contentInfo->id);
        $this->assertEquals($parentLocationId, $location->parentLocationId);
    }

    public function testCreateContentAndLocation()
    {
        $locationRemoteId = '_test_location_content_integration_2';
        $locationParentId = 58;

        $contentRemoteId = '_test_content_location_integration_2';
        $contentFields = array('title' => 'Test title');
        $contentTypeIdentifier = ContentTestCase::_content_type_article;

        $locationObject = $this->getLocationObject(
            $locationRemoteId,
            false,
            $locationParentId
        );
        $contentObject = $this->getContentObject(
            $contentFields,
            $contentRemoteId,
            $contentTypeIdentifier,
            'eng-GB',
            array(2, $locationObject)
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $newLocation1 = static::$repository->getLocationService()->loadLocationByRemoteId($locationRemoteId);
        $newContent = static::$repository->getContentService()->loadContentByRemoteId($contentRemoteId);

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
