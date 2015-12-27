<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Repository;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Repository\ObjectService;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class ObjectServiceTest extends EzPlatformTestCase
{
    /**
     * @var ObjectService
     */
    protected $service;

    public function setUp()
    {
        /** @var Repository $repository */
        $repository = $this->getMock('eZ\Publish\API\Repository\Repository');
        $this->service = new ObjectService($repository);

        $this->service->setLogger($this->getMock('Psr\Log\LoggerInterface'));
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

        $this->assertInstanceOf('Transfer\EzPlatform\Repository\Manager\ContentManager', $contentManager);
        $this->assertInstanceOf('Transfer\EzPlatform\Repository\Manager\LocationManager', $locationManager);
        $this->assertInstanceOf('Transfer\EzPlatform\Repository\Manager\ContentTypeManager', $contentTypeManager);
        $this->assertInstanceOf('Transfer\EzPlatform\Repository\Manager\LanguageManager', $languageManager);
    }

    public function testCreateWithNullArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->service->create(null);
    }
}
