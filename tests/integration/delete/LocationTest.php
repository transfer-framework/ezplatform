<?php

namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\LocationObject;
use Transfer\EzPlatform\tests\testcase\LocationTestCase;

class LocationTest extends LocationTestCase
{
    public function testDelete()
    {
        $location = static::$repository->getLocationService()->loadLocationByRemoteId($this->_test_location_remote_id_2);
        $locationObject = new LocationObject($location);

        $locationObject->setProperty('action', Action::DELETE);

        $this->adapter->send(new Request(array(
            $locationObject,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getLocationService()->loadLocationByRemoteId($this->_test_location_remote_id_2);
    }
}
