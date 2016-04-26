<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SetupFactory;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\LocationObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
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

        static::setUpContentTypes();

        static::$hasDatabase = true;
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
    }

    public static function setUpContentTypes()
    {
        $_ct_article = new ContentTypeObject(array(
            'identifier' => '_test_article',
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'names' => array('eng-GB' => 'Article'),
            'descriptions' => array('eng-GB' => 'An article'),
            'is_container' => true,
            'default_always_available' => false,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'title' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Title'),
                    'descriptions' => array('eng-GB' => 'Title of the article'),
                    'field_group' => 'content',
                    'position' => 10,
                    'is_required' => true,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
                'description' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Description'),
                    'descriptions' => array('eng-GB' => 'Description of the article'),
                    'field_group' => 'content',
                    'position' => 20,
                    'is_required' => false,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,

                ),
            ),
        ));

        static::$contentTypeManager->createOrUpdate($_ct_article);
    }

    /**
     * Creates a TreeObject skeleton.
     *
     * @param int   $locationId
     * @param array $data
     *
     * @return TreeObject
     */
    protected function getTreeObject($locationId, $data)
    {
        $tree = new TreeObject($data);
        $tree->setProperty('parent_location_id', $locationId);

        return $tree;
    }

    /**
     * Creates a ContentObject skeleton.
     *
     * @param array  $data
     * @param string $contenttype
     * @param string $remoteId
     *
     * @return ContentObject
     */
    protected function getContentObject(array $data, $contenttype, $remoteId)
    {
        $content = new ContentObject($data);
        $content->setProperty('content_type_identifier', $contenttype);
        $content->setProperty('remote_id', $remoteId);
        $content->setProperty('language', 'eng-GB');

        return $content;
    }

    /**
     * @param int    $parentLocationId
     * @param string $remoteId
     *
     * @return LocationObject
     */
    protected function getLocationObject($parentLocationId, $remoteId)
    {
        return new LocationObject([
            'remote_id' => $remoteId,
            'parent_location_id' => $parentLocationId,
        ]);
    }

    /**
     * @param $content
     *
     * @return string
     */
    protected function getRichtext($content)
    {
        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">%s</section>
',
            $content);
    }
}
