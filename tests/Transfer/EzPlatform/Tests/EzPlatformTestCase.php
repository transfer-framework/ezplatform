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
use Transfer\EzPlatform\Data\FieldDefinitionObject;
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
        $_ct_article = new ContentTypeObject('_test_article');
        $_ct_article->mainLanguageCode = 'eng-GB';
        $_ct_article->addContentTypeGroup('Content');
        $_ct_article->nameSchema = '<title>';
        $_ct_article->urlAliasSchema = '<title>';
        $_ct_article->addName('Article');
        $_ct_article->addDescription('An article');

        $_fd_name = new FieldDefinitionObject('title');
        $_fd_name->addName('Title');
        $_fd_name->addDescription('Title of the article');
        $_fd_name->fieldGroup = 'content';
        $_fd_name->position = 10;
        $_fd_name->isRequired = true;
        $_fd_name->isTranslatable = true;
        $_fd_name->isSearchable = true;
        $_fd_name->isInfoCollector = false;
        $_ct_article->addFieldDefinition($_fd_name);

        $_fd_desc = new FieldDefinitionObject('description');
        $_fd_desc->addName('Description');
        $_fd_desc->addDescription('Description of the article');
        $_fd_desc->fieldGroup = 'content';
        $_fd_desc->position = 20;
        $_fd_desc->isRequired = false;
        $_fd_desc->isTranslatable = true;
        $_fd_desc->isSearchable = true;
        $_fd_desc->isInfoCollector = false;
        $_ct_article->addFieldDefinition($_fd_desc);

        static::$contentTypeManager->createOrUpdate($_ct_article);
    }
}
