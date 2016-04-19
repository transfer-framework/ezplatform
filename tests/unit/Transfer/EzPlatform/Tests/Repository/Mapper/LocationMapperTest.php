<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationUpdateStruct;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * User manager tests.
 */
class LocationMapperTest extends EzPlatformTestCase
{
    public function testLocationObjectFromLocation()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $locationObject = new LocationObject($location);

        $this->assertEquals($location->id, $locationObject->data['id']);
        $this->assertEquals($location->remoteId, $locationObject->data['remote_id']);
        $this->assertEquals($location->contentId, $locationObject->data['content_id']);
        $this->assertEquals($location->parentLocationId, $locationObject->data['parent_location_id']);
        $this->assertEquals($location->hidden, $locationObject->data['hidden']);
        $this->assertEquals($location->depth, $locationObject->data['depth']);
        $this->assertEquals($location->sortOrder, $locationObject->data['sort_order']);
        $this->assertEquals($location->sortField, $locationObject->data['sort_field']);

        $this->assertEquals($location->contentInfo, $locationObject->getProperty('content_info'));
        $this->assertEquals($location->invisible, $locationObject->getProperty('invisible'));
        $this->assertEquals($location->path, $locationObject->getProperty('path'));
        $this->assertEquals($location->pathString, $locationObject->getProperty('path_string'));
    }

    public function testLocationToLocationObject()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $locationObject = new LocationObject(array());
        $locationObject->getMapper()->locationToObject($location);

        $this->assertEquals($location->id, $locationObject->data['id']);
        $this->assertEquals($location->remoteId, $locationObject->data['remote_id']);
        $this->assertEquals($location->contentId, $locationObject->data['content_id']);
        $this->assertEquals($location->parentLocationId, $locationObject->data['parent_location_id']);
        $this->assertEquals($location->hidden, $locationObject->data['hidden']);
        $this->assertEquals($location->depth, $locationObject->data['depth']);
        $this->assertEquals($location->sortOrder, $locationObject->data['sort_order']);
        $this->assertEquals($location->sortField, $locationObject->data['sort_field']);

        $this->assertEquals($location->contentInfo, $locationObject->getProperty('content_info'));
        $this->assertEquals($location->invisible, $locationObject->getProperty('invisible'));
        $this->assertEquals($location->path, $locationObject->getProperty('path'));
        $this->assertEquals($location->pathString, $locationObject->getProperty('path_string'));
    }

    public function testLocationCreateStruct()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $locationObject = new LocationObject(array());
        $locationObject->getMapper()->locationToObject($location);

        $locationCreateStruct = new LocationCreateStruct();
        $locationObject->getMapper()->getNewLocationCreateStruct($locationCreateStruct);

        $this->assertEquals($location->remoteId, $locationCreateStruct->remoteId);
        $this->assertEquals($location->hidden, $locationCreateStruct->hidden);
        $this->assertEquals($location->priority, $locationCreateStruct->priority);
        $this->assertEquals($location->sortField, $locationCreateStruct->sortField);
        $this->assertEquals($location->sortOrder, $locationCreateStruct->sortOrder);
    }

    public function testLocationUpdateStruct()
    {
        $location = static::$repository->getLocationService()->loadLocation(2);
        $locationObject = new LocationObject(array());
        $locationObject->getMapper()->locationToObject($location);

        $locationUpdateStruct = new LocationUpdateStruct();
        $locationObject->getMapper()->getNewLocationUpdateStruct($locationUpdateStruct);

        $this->assertEquals($location->remoteId, $locationUpdateStruct->remoteId);
        $this->assertEquals($location->priority, $locationUpdateStruct->priority);
        $this->assertEquals($location->sortField, $locationUpdateStruct->sortField);
        $this->assertEquals($location->sortOrder, $locationUpdateStruct->sortOrder);
    }
}
