<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class LocationManagerTest extends EzPlatformTestCase
{
    /**
     * @var LocationManager
     */
    private $manager;

    public function setUp()
    {
        $locationMock = $this->getMock('eZ\Publish\API\Repository\Values\Content\Location');

        $locationServiceMock = $this->getMock('eZ\Publish\API\Repository\LocationService');
        $locationServiceMock->method('hideLocation')->willReturn($locationMock);
        $locationServiceMock->method('unhideLocation')->willReturn($locationMock);

        $repositoryMock = $this->getMock('eZ\Publish\API\Repository\Repository');
        $repositoryMock->method('getLocationService')->willReturn($locationServiceMock);

        $this->manager = new LocationManager($repositoryMock);
        $this->manager->setLogger($this->getMock('Psr\Log\LoggerInterface'));
    }

    public function testCreate()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UnsupportedOperationException');

        $this->manager->create(new ValueObject(null));
    }

    public function testRemove()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\UnsupportedOperationException');

        $this->manager->remove(new ValueObject($this->getMock('eZ\Publish\API\Repository\Values\Content\Location')));
    }

    public function testHide()
    {
        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\Content\Location',
            $this->manager->hide(new LocationObject($this->getMock('eZ\Publish\API\Repository\Values\Content\Location')))
        );
    }

    public function testUnHide()
    {
        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\Content\Location',
            $this->manager->unHide(new LocationObject($this->getMock('eZ\Publish\API\Repository\Values\Content\Location')))
        );
    }

    public function testToggleVisibility()
    {
        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\Content\Location',
            $this->manager->toggleVisibility(new LocationObject($this->getMock('eZ\Publish\API\Repository\Values\Content\Location')))
        );

        $location = $this->getMock('eZ\Publish\API\Repository\Values\Content\Location');
        $location->method('__get')->willReturn(true);

        $this->assertInstanceOf(
            'eZ\Publish\API\Repository\Values\Content\Location',
            $this->manager->toggleVisibility(new LocationObject($location))
        );
    }
}
