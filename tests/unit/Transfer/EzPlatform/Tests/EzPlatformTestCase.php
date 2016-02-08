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
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Repository\Manager\LanguageManager;
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
     * @var EzPlatformAdapter
     */
    protected $adapter;

    /**
     * @var Repository
     */
    protected static $repository;

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

        static::setUpContentTypes();

        static::$hasDatabase = true;
    }

    public static function setUpContentTypes()
    {
        $_ct_article = new ContentTypeObject('_test_article', array(
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
        $tree->setProperty('location_id', $locationId);

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
        $content->setContentType($contenttype);
        $content->setRemoteId($remoteId);
        $content->setLanguage('eng-GB');

        return $content;
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
