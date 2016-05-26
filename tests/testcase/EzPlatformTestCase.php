<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SetupFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\Core\ContentTreeService;
use Transfer\EzPlatform\Repository\Manager\Core\ObjectService;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
use Transfer\EzPlatform\Repository\Manager\LocationManager;
use Transfer\EzPlatform\Repository\Manager\UserGroupManager;
use Transfer\EzPlatform\Repository\Manager\UserManager;

/**
 * Common eZ Platform test case.
 */
abstract class EzPlatformTestCase extends KernelTestCase
{
    /**
     * @var Container
     */
    protected $container;

    /**
     * @var Repository
     */
    protected static $repository;

    /**
     * @var LocationManager
     */
    protected static $locationManager;

    /**
     * @var ContentManager
     */
    protected static $contentManager;

    /**
     * @var ContentTypeManager
     */
    protected static $contentTypeManager;

    /**
     * @var LanguageManager
     */
    protected static $languageManager;

    /**
     * @var UserGroupManager
     */
    protected static $userGroupManager;

    /**
     * @var UserManager
     */
    protected static $userManager;

    /**
     * @var ObjectService
     */
    protected static $objectService;

    /**
     * @var ContentTreeService
     */
    protected static $contentTreeService;

    /**
     * @var bool
     */
    protected static $hasDatabase;

    public static function setUpBeforeClass()
    {
        if (static::$hasDatabase) {
            return;
        }

        $setupFactory = new SetupFactory();
        static::$repository = $setupFactory->getRepository();

        static::$languageManager = new LanguageManager(static::$repository);
        static::$contentTypeManager = new ContentTypeManager(static::$repository, static::$languageManager);
        static::$userGroupManager = new UserGroupManager(static::$repository);
        static::$userManager = new UserManager(static::$repository, static::$userGroupManager);
        static::$locationManager = new LocationManager(static::$repository);
        static::$contentManager = new ContentManager(static::$repository, static::$locationManager);

        static::$objectService = new ObjectService(static::$repository);
        static::$contentTreeService = new ContentTreeService(static::$repository, static::$objectService);

        static::$hasDatabase = true;
    }

    public function setUp()
    {
        parent::setUp();
        $this->setLoggers();
    }

    protected function setLoggers()
    {
        $logger = $this->getMock(LoggerInterface::class);
        static::$languageManager->setLogger($logger);
        static::$contentTypeManager->setLogger($logger);
        static::$userGroupManager->setLogger($logger);
        static::$userManager->setLogger($logger);
        static::$locationManager->setLogger($logger);
        static::$contentManager->setLogger($logger);

        static::$objectService->setLogger($logger);
        static::$contentTreeService->setLogger($logger);
    }
}
