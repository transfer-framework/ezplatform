<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Content type manager tests.
 */
class ContentTypeManagerTest extends EzPlatformTestCase
{
    public function testCreate()
    {
        $manager = new ContentTypeManager(static::$repository);

        $this->createOrUpdate($manager);
        $this->delete($manager);

        $this->create($manager);

        $contentType = $manager->findByIdentifier('frontpage');
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\ContentType', $contentType);
        $this->assertEquals('Frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('A frontpage', $contentType->getDescription('eng-GB'));
        $contentTypeGroups = $contentType->getContentTypeGroups();
        $this->assertEquals('Content', $contentTypeGroups[0]->identifier);
        $this->assertEquals('<name>', $contentType->urlAliasSchema);
        $this->assertEquals('<name>', $contentType->nameSchema);
        $this->assertEquals('eng-GB', $contentType->mainLanguageCode);
        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('title', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Name of the frontpage', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertTrue($contentFieldDefinition->isTranslatable);
        $this->assertTrue($contentFieldDefinition->isRequired);
        $this->assertTrue($contentFieldDefinition->isSearchable);
    }

    public function testUpdate()
    {
        $manager = new ContentTypeManager(static::$repository);

        $this->createOrUpdate($manager);

        $this->delete($manager);

        $this->create($manager);

        $this->update($manager);
    }

    protected function create(ContentTypeManager $manager)
    {
        return $manager->create(
            new ContentTypeObject(array(
                'group_identifier' => 'Content',
                'identifier' => 'frontpage',
                'main_language_code' => 'eng-GB',
                'name_schema' => '<name>',
                'url_alias_schema' => '<name>',
                'names' => array(
                    'eng-GB' => 'Frontpage',
                ),
                'descriptions' => array(
                    'eng-GB' => 'A frontpage',
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
                ),
            ))
        );
    }

    protected function createOrUpdate(ContentTypeManager $manager)
    {
        return $manager->createOrUpdate(
            new ContentTypeObject(array(
                'group_identifier' => 'Content',
                'identifier' => 'frontpage',
                'main_language_code' => 'eng-GB',
                'name_schema' => '<name>',
                'url_alias_schema' => '<name>',
                'names' => array(
                    'eng-GB' => 'Frontpage',
                ),
                'descriptions' => array(
                    'eng-GB' => 'A frontpage',
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

    protected function update(ContentTypeManager $manager)
    {
        return $manager->update(
            new ContentTypeObject(array(
                'group_identifier' => 'Content',
                'identifier' => 'frontpage',
                'main_language_code' => 'eng-GB',
                'name_schema' => '<name>',
                'url_alias_schema' => '<name>',
                'names' => array(
                    'eng-GB' => 'Frontpage',
                ),
                'descriptions' => array(
                    'eng-GB' => 'A frontpage',
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

    protected function find(ContentTypeManager $manager)
    {
        return $manager->findByIdentifier('frontpage');
    }

    protected function delete(ContentTypeManager $manager)
    {
        $manager->remove(
            new ContentTypeObject(array(
                'identifier' => 'frontpage',
            ))
        );
    }
}
