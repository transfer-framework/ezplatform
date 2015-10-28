<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\Core\Repository\Tests\Service\Integration\Legacy\SetupFactory;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Container;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;

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

        static::setUpContentTypes();

        static::$hasDatabase = true;
    }

    public static function setUpContentTypes()
    {
        $manager = new ContentTypeManager(static::$repository);

        $manager->create(
            new ContentTypeObject(array(
                'group_identifier' => 'Content',
                'identifier' => '_test_article',
                'main_language_code' => 'eng-GB',
                'name_schema' => '<name>',
                'url_alias_schema' => '<name>',
                'names' => array(
                    'eng-GB' => 'Article',
                ),
                'descriptions' => array(
                    'eng-GB' => 'An Article',
                ),
                'fields' => array(
                    'title' => array(
                        'type' => 'ezstring',
                        'names' => array(
                            'eng-GB' => 'Name',
                        ),
                        'descriptions' => array(
                            'eng-GB' => 'Name of the frontpage',
                        ),
                        'field_group' => 'content',
                        'position' => 10,
                        'is_translatable' => true,
                        'is_required' => true,
                        'is_searchable' => true,
                    ),
                    'description' => array(
                        'type' => 'ezstring',
                        'names' => array(
                            'eng-GB' => 'Description',
                        ),
                        'descriptions' => array(
                            'eng-GB' => 'Description of the frontpage',
                        ),
                        'field_group' => 'content',
                        'position' => 20,
                        'is_translatable' => true,
                        'is_required' => false,
                        'is_searchable' => true,
                    ),
                ),
            ))
        );
    }
}
