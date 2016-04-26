<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Psr\Log\AbstractLogger;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Exception\LanguageNotFoundException;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\tests\EzPlatformTestCase;

/**
 * Content type manager tests.
 */
class ContentTypeManagerTest extends EzPlatformTestCase
{
    public function testUnknownLanguage()
    {
        $manager = static::$contentTypeManager;
        $this->setExpectedException(LanguageNotFoundException::class, 'Default language name for code "test-TEST" not found.');
        $frontpage = $this->getFrontpageContentTypeObject();
        $frontpage->data['names'] = ['test-TEST' => 'My test language'];
        $manager->createOrUpdate($frontpage);
    }

    public function testSeveralLanguages()
    {
        $manager = static::$contentTypeManager;

        $frontpage = $this->getFrontpageContentTypeObject();
        $frontpage->data['names'] = array(
            'eng-GB' => 'Frontpage',
            'ger-DE' => 'Titelseite',
            'nor-NO' => 'Forside',
        );
        $frontpage->data['descriptions'] = array(
            'eng-GB' => 'Frontpage description',
            'ger-DE' => 'Beschreibung',
            'nor-NO' => 'Forsidebeskrivelse',
        );
        $manager->createOrUpdate($frontpage);

        $ct = $manager->find(new ValueObject(['identifier' => 'frontpage']));
        $this->assertEquals('Titelseite', $ct->getName('ger-DE'));
        $this->assertEquals('Beschreibung', $ct->getDescription('ger-DE'));
        $this->assertEquals('Forside', $ct->getName('nor-NO'));
        $this->assertEquals('Forsidebeskrivelse', $ct->getDescription('nor-NO'));
    }

    public function testCreateMultipleContentTypeGroups()
    {
        $manager = static::$contentTypeManager;
        $ct = $this->getFrontpageContentTypeObject();
        $ct->data['contenttype_groups'][] = 'MyGroup1';
        $ct->data['contenttype_groups'][] = 'MyGroup2';
        $manager->createOrUpdate($ct);
        $contentType = $manager->find(new ValueObject(['identifier' => 'frontpage']));
        $this->assertEquals('Content', $contentType->contentTypeGroups[0]->identifier);
        $this->assertEquals('MyGroup1', $contentType->contentTypeGroups[1]->identifier);
        $this->assertEquals('MyGroup2', $contentType->contentTypeGroups[2]->identifier);
    }

    public function testCreateContentTypeGroup()
    {
        $manager = static::$contentTypeManager;

        $ct = $this->getFrontpageContentTypeObject();
        $ct->data['contenttype_groups'] = array('FrontpageGroup');
        $manager->createOrUpdate($ct);
        $contentType = $manager->find(new ValueObject(['identifier' => 'frontpage']));
        $this->assertEquals('FrontpageGroup', $contentType->contentTypeGroups[0]->identifier);
    }

    public function testDeleteNotFound()
    {
        $manager = static::$contentTypeManager;
        $this->assertTrue($manager->removeContentTypeByIdentifier(null));
        $this->assertTrue($manager->removeContentTypeByIdentifier('_i_dont_exist'));
    }

    public function testLogger()
    {
        $manager = static::$contentTypeManager;
        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $manager->setLogger($mockLogger);
    }

    public function testfindNotFound()
    {
        $manager = static::$contentTypeManager;
        $result = $manager->find(new ValueObject([]));
        $this->assertFalse($result);
    }

    public function testCreate()
    {
        $manager = static::$contentTypeManager;

        $this->createOrUpdate($manager);
        $this->remove($manager);

        $this->create($manager);

        $contentType = $manager->find(new ValueObject(['identifier' => 'frontpage']));
        $this->assertInstanceOf(ContentType::class, $contentType);
        $this->assertEquals('Frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('Frontpage description', $contentType->getDescription('eng-GB'));
        $contentTypeGroups = $contentType->getContentTypeGroups();
        $this->assertEquals('Content', $contentTypeGroups[0]->identifier);
        $this->assertEquals('<title>', $contentType->urlAliasSchema);
        $this->assertEquals('<title>', $contentType->nameSchema);
        $this->assertEquals('eng-GB', $contentType->mainLanguageCode);
        $this->assertFalse($contentType->isContainer);

        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf(FieldDefinition::class, $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Name description', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertFalse($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $this->update($manager);

        $contentType = $manager->find(new ValueObject(['identifier' => 'frontpage']));
        $this->assertEquals('Updated frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('Updated frontpage description', $contentType->getDescription('eng-GB'));
        $this->assertFalse($contentType->isContainer);

        $this->assertCount(2, $contentType->fieldDefinitions);
        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf(FieldDefinition::class, $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Updated name description', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertFalse($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertFalse($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $contentFieldDefinition = $contentType->fieldDefinitions[1];
        $this->assertInstanceOf(FieldDefinition::class, $contentFieldDefinition);
        $this->assertEquals('short_description', $contentFieldDefinition->identifier);
        $this->assertEquals('Short description', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertTrue($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertTrue($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);
    }

    public function testUpdateWithLogger()
    {
        $manager = static::$contentTypeManager;
        $mockLogger = $this->getMock(AbstractLogger::class, array('log'), array(), '', false);
        $manager->setLogger($mockLogger);

        $this->createOrUpdate($manager);

        $this->remove($manager);

        $this->create($manager);

        $this->update($manager);
    }

    public function testUpdate()
    {
        $manager = static::$contentTypeManager;

        $this->createOrUpdate($manager);

        $this->remove($manager);

        $this->create($manager);

        $this->update($manager);
    }

    protected function create(ContentTypeManager $manager)
    {
        $ct = $this->getFrontpageContentTypeObject();

        return $manager->create($ct);
    }

    protected function createOrUpdate(ContentTypeManager $manager)
    {
        $ct = $this->getFrontpageContentTypeObject();

        return $manager->createOrUpdate($ct);
    }

    protected function update(ContentTypeManager $manager)
    {
        $ct = $this->getUpdatedFrontpageContentTypeObject();

        return $manager->update($ct);
    }

    protected function find(ContentTypeManager $manager)
    {
        return $manager->findContentTypeByIdentifier('frontpage');
    }

    protected function remove(ContentTypeManager $manager)
    {
        $manager->remove($this->getFrontpageContentTypeObject());
    }

    /**
     * @return ContentTypeObject
     */
    protected function getFrontpageContentTypeObject()
    {
        return new ContentTypeObject($this->getFrontpageContentTypeDataArray('frontpage'));
    }

    protected function getFrontpageContentTypeDataArray($identifier)
    {
        return array(
            'identifier' => $identifier,
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'names' => array('eng-GB' => 'Frontpage'),
            'descriptions' => array('eng-GB' => 'Frontpage description'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'is_container' => false,
            'default_always_available' => true,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'name' => array(
                    'type' => 'ezstring',
                    'field_group' => 'content',
                    'position' => 0,
                    'names' => array('eng-GB' => 'Name'),
                    'descriptions' => array('eng-GB' => 'Name description'),
                    'default_value' => null,
                    'is_required' => false,
                    'is_translatable' => false,
                    'is_searchable' => false,
                    'is_info_collector' => false,
                ),
                'short_description' => array(
                    'type' => 'ezstring',
                    'field_group' => 'content',
                    'position' => 20,
                    'names' => array('eng-GB' => 'Short description'),
                    'descriptions' => array('eng-GB' => ''),
                    'default_value' => '',
                    'is_required' => false,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
            ),
        );
    }

    /**
     * @return ContentTypeObject
     */
    protected function getUpdatedFrontpageContentTypeObject()
    {
        $frontPage = $this->getFrontpageContentTypeObject();
        $frontPage->data['names'] = array('eng-GB' => 'Updated frontpage');
        $frontPage->data['description'] = array('eng-GB' => 'Updated frontpage description');
        $frontPage->fields[0]->data['names'] = array('eng-GB' => 'Updated name');
        $frontPage->fields[0]->data['descriptions'] = array('eng-GB' => 'Updated name description');

        return new ContentTypeObject(array(
            'identifier' => 'frontpage',
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'names' => array('eng-GB' => 'Updated frontpage'),
            'descriptions' => array('eng-GB' => 'Updated frontpage description'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'is_container' => false,
            'default_always_available' => true,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'name' => array(
                    'type' => 'ezstring',
                    'field_group' => 'content',
                    'position' => 0,
                    'names' => array('eng-GB' => 'Name'),
                    'descriptions' => array('eng-GB' => 'Updated name description'),
                    'default_value' => null,
                    'is_required' => false,
                    'is_translatable' => false,
                    'is_searchable' => false,
                    'is_info_collector' => false,
                ),
                'short_description' => array(
                    'type' => 'ezstring',
                    'field_group' => 'content',
                    'position' => 20,
                    'names' => array('eng-GB' => 'Short description'),
                    'descriptions' => array('eng-GB' => ''),
                    'default_value' => '',
                    'is_required' => false,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),

            ),
        ));
    }
}
