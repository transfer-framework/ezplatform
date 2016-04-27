<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use Transfer\EzPlatform\Repository\AbstractRepositoryService;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class AbstractRepositoryServiceTest extends EzPlatformTestCase
{
    /** @var AbstractRepositoryService $mock */
    private $mock;

    public function setUp()
    {
        $repositoryMock = $this
            ->getMockBuilder('eZ\Publish\API\Repository\Repository')
            ->getMock();

        $repositoryMock
            ->method('getContentTypeService')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\ContentTypeService'));

        $repositoryMock
            ->method('getContentService')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\ContentService'));

        $repositoryMock
            ->method('getLocationService')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\LocationService'));

        $repositoryMock
            ->method('getUserService')
            ->willReturn($this->getMock('eZ\Publish\API\Repository\UserService'));

        $this->mock = $this
            ->getMockForAbstractClass('Transfer\EzPlatform\Repository\AbstractRepositoryService', array($repositoryMock));
    }

    public function testGetRepository()
    {
        $this->assertInstanceOf('eZ\Publish\API\Repository\Repository', $this->mock->getRepository());
    }

    public function testGetContentTypeService()
    {
        $this->assertInstanceOf('eZ\Publish\API\Repository\ContentTypeService', $this->mock->getContentTypeService());
    }

    public function testGetContentService()
    {
        $this->assertInstanceOf('eZ\Publish\API\Repository\ContentService', $this->mock->getContentService());
    }

    public function testGetLocationService()
    {
        $this->assertInstanceOf('eZ\Publish\API\Repository\LocationService', $this->mock->getLocationService());
    }

    public function testGetUserService()
    {
        $this->assertInstanceOf('eZ\Publish\API\Repository\UserService', $this->mock->getUserService());
    }
}
