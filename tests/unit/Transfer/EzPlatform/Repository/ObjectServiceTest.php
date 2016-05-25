<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Repository\Manager\UserGroupManager;
use Transfer\EzPlatform\Repository\Manager\UserManager;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ObjectServiceTest extends EzPlatformTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testGetters()
    {
        $contentManager = static::$objectService->getContentManager();
        $locationManager = static::$objectService->getLocationManager();
        $contentTypeManager = static::$objectService->getContentTypeManager();
        $languageManager = static::$objectService->getLanguageManager();
        $userManager = static::$objectService->getUserManager();
        $userGroupManager = static::$objectService->getUserGroupManager();

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

        static::$objectService->createOrUpdate(null);
    }
}
