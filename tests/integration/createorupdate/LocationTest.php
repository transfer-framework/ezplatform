<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\LocationTestCase;

class LocationTest extends LocationTestCase
{
    /**
     * Tests location creation.
     */
    public function testCreateLocation()
    {
        $remoteId = $this->_test_location_remote_id_1;
        $parentLocationId = 60;

        $locationObject = $this->getLocationObject($remoteId, $this->_test_content_id_1, $parentLocationId);

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId($remoteId);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($this->_test_content_id_1, $location->contentInfo->id);
        $this->assertEquals($parentLocationId, $location->parentLocationId);
    }

    public function testUpdateLocation()
    {
        $remoteId = $this->_test_location_remote_id_1;
        $parentLocationId = 64;

        $locationObject = $this->getLocationObject($remoteId, $this->_test_content_id_1, $parentLocationId);

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId($remoteId);

        $this->assertInstanceOf(Location::class, $location);
        $this->assertEquals($this->_test_content_id_1, $location->contentInfo->id);
        $this->assertEquals($parentLocationId, $location->parentLocationId);
    }

    public function testCreateContentAndLocation()
    {
        $locationRemoteId = $this->_test_location_remote_id_3;
        $locationParentId = 58;

        $contentRemoteId = $this->_test_content_remote_id_3;
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

    public function testUpdateContentWithMoreLocations()
    {
        $content = static::$repository->getContentService()->loadContentByRemoteId($this->_test_content_remote_id_3);
        $locations = static::$repository->getLocationService()->loadLocations($content->contentInfo);

        $countLocations = count($locations);

        $contentObject = new ContentObject($content, array(
            'parent_locations' => $locations,
        ));

        $contentObject->addParentLocation(65);

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $this->assertCount(
            $countLocations + 1,
            static::$repository->getLocationService()->loadLocations($contentObject->getProperty('content_info'))
        );
    }

    /**
     * Tests location struct callback.
     */
    public function testStructCallback()
    {
        $remoteId = $this->_test_location_remote_id_1;
        $parentLocationId = 2;

        $locationObject = $this->getLocationObject($remoteId, $this->_test_content_id_1, $parentLocationId);
        $locationObject->setProperty('struct_callback', function (LocationUpdateStruct $struct) {
            $struct->priority = 1000;
        });

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $location = static::$repository->getLocationService()->loadLocationByRemoteId($remoteId);

        $this->assertEquals(1000, $location->priority);
    }
}
