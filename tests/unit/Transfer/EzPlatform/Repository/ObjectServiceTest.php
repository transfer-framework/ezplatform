<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use eZ\Publish\API\Repository\Repository;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Repository\Manager\UserGroupManager;
use Transfer\EzPlatform\Repository\Manager\UserManager;
use Transfer\EzPlatform\Repository\ObjectService;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ObjectServiceTest extends EzPlatformTestCase
{
    /**
     * @var ObjectService
     */
    protected $service;

    public function setUp()
    {
        /** @var Repository $repository */
        $repository = $this->getMock(Repository::class);
        $this->service = new ObjectService($repository);

        $this->service->setLogger($this->getMock(LoggerInterface::class));
    }

    public function testGetters()
    {
        $contentManager = $this->service->getContentManager();

        // Simulate several calls
        $this->service->getLocationManager();
        $locationManager = $this->service->getLocationManager();

        $this->service->getContentTypeManager();
        $contentTypeManager = $this->service->getContentTypeManager();

        $this->service->getLanguageManager();
        $languageManager = $this->service->getLanguageManager();

        $this->service->getUserManager();
        $userManager = $this->service->getUserManager();

        $this->service->getUserGroupManager();
        $userGroupManager = $this->service->getUserGroupManager();

        $this->assertInstanceOf(ContentManager::class, $contentManager);
        $this->assertInstanceOf(LocationManager::class, $locationManager);
        $this->assertInstanceOf(ContentTypeManager::class, $contentTypeManager);
        $this->assertInstanceOf(LanguageManager::class, $languageManager);
        $this->assertInstanceOf(UserManager::class, $userManager);
        $this->assertInstanceOf(UserGroupManager::class, $userGroupManager);
    }

    public function testCreateWithNullArgument()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->service->createOrUpdate(null);
    }
}
