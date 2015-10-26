<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\ContentManager;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Content manager tests.
 */
class ContentManagerTest extends EzPlatformTestCase
{
    public static function setUpBeforeClass()
    {
        parent::setUpBeforeClass();

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

    public function testCreate()
    {
        $manager = new ContentManager(static::$repository);

        $contentObject = new ContentObject(array(
            'title' => 'Test title',
            'description' => 'Test description',
        ));
        $contentObject->setContentType('_test_article');
        $contentObject->setLanguage('eng-GB');
        $contentObject->setRemoteId('_test_1');

        $manager->create($contentObject);

        $createdContentObject = $manager->findByRemoteId('_test_1');

        $this->assertEquals('Test title', (string) $createdContentObject->data['title']['eng-GB']);
        $this->assertEquals('Test description', (string) $createdContentObject->data['description']['eng-GB']);
    }
}
