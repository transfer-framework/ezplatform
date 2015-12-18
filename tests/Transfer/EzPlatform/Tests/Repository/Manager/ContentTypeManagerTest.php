<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Data\FieldDefinitionObject;
use Transfer\EzPlatform\Repository\Content\FieldDefinitionRepository;
use Transfer\EzPlatform\Repository\Manager\ContentTypeManager;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Content type manager tests.
 */
class ContentTypeManagerTest extends EzPlatformTestCase
{

    public function testDeleteNotFound()
    {
        $manager = new ContentTypeManager(static::$repository);
        $this->assertTrue($manager->removeByIdentifier(null));
        $this->assertTrue($manager->removeByIdentifier('_i_dont_exist'));
    }

    public function testDuplicateField()
    {
        $manager = new ContentTypeManager(static::$repository);

        $this->createOrUpdate($manager);
        $this->delete($manager);

        $ct = $this->getFrontpageContentTypeObject();
        $fd0 = $ct->getFieldDefinitions()[0];
        $fd1 = $ct->getFieldDefinitions()[1];
        $ct->setFieldDefinitions(array($fd0, $fd1));
        $ct->addFieldDefinition($fd0);
        $manager->create($ct);

        $contentType = $manager->findByIdentifier('frontpage');
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\ContentType', $contentType);
        $this->assertEquals('Frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('A frontpage', $contentType->getDescription('eng-GB'));
        $contentTypeGroups = $contentType->getContentTypeGroups();
        $this->assertEquals('Content', $contentTypeGroups[0]->identifier);
        $this->assertEquals('<name>', $contentType->urlAliasSchema);
        $this->assertEquals('<name>', $contentType->nameSchema);
        $this->assertEquals('eng-GB', $contentType->mainLanguageCode);
        $this->assertTrue($contentType->isContainer);
        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Name of the frontpage', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertTrue($contentFieldDefinition->isTranslatable);
        $this->assertTrue($contentFieldDefinition->isRequired);
        $this->assertTrue($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $this->update($manager);

        $contentType = $manager->findByIdentifier('frontpage');
        $this->assertEquals('Updated frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('Updated frontpage description', $contentType->getDescription('eng-GB'));
        $this->assertFalse($contentType->isContainer);

        $this->assertCount(3, $contentType->fieldDefinitions);
        $contentFieldDefinition = $contentType->fieldDefinitions[1];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Updated name description', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertFalse($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertFalse($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('short_description', $contentFieldDefinition->identifier);
        $this->assertEquals('Short description', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertTrue($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertTrue($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);
    }

    public function testLogger()
    {
        $manager = new ContentTypeManager(static::$repository);
        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $manager->setLogger($mockLogger);
    }

    public function testfindNotFound()
    {
        $manager = new ContentTypeManager(static::$repository);
        $result = $manager->findByIdentifier(null);
        $this->assertFalse($result);
    }

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
        $this->assertTrue($contentType->isContainer);
        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Name of the frontpage', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertTrue($contentFieldDefinition->isTranslatable);
        $this->assertTrue($contentFieldDefinition->isRequired);
        $this->assertTrue($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $this->update($manager);

        $contentType = $manager->findByIdentifier('frontpage');
        $this->assertEquals('Updated frontpage', $contentType->getName('eng-GB'));
        $this->assertEquals('Updated frontpage description', $contentType->getDescription('eng-GB'));
        $this->assertFalse($contentType->isContainer);

        $this->assertCount(3, $contentType->fieldDefinitions);
        $contentFieldDefinition = $contentType->fieldDefinitions[1];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
        $this->assertEquals('name', $contentFieldDefinition->identifier);
        $this->assertEquals('Name', $contentFieldDefinition->getName('eng-GB'));
        $this->assertEquals('Updated name description', $contentFieldDefinition->getDescription('eng-GB'));
        $this->assertEquals('ezstring', $contentFieldDefinition->fieldTypeIdentifier);
        $this->assertFalse($contentFieldDefinition->isTranslatable);
        $this->assertFalse($contentFieldDefinition->isRequired);
        $this->assertFalse($contentFieldDefinition->isSearchable);
        $this->assertFalse($contentFieldDefinition->isInfoCollector);

        $contentFieldDefinition = $contentType->fieldDefinitions[0];
        $this->assertInstanceOf('eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition', $contentFieldDefinition);
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
        $manager = new ContentTypeManager(static::$repository);
        $mockLogger = $this->getMock('Psr\Log\AbstractLogger', array('log'), array(), '', false);
        $manager->setLogger($mockLogger);

        $this->createOrUpdate($manager);

        $this->delete($manager);

        $this->create($manager);

        $this->update($manager);
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
        return $manager->findByIdentifier('frontpage');
    }

    protected function delete(ContentTypeManager $manager)
    {
        $manager->removeByIdentifier(
            $this->getFrontpageContentTypeObject()->getIdentifier()
        );
    }

    /**
     * @return ContentTypeObject
     */
    protected function getFrontpageContentTypeObject()
    {
        $ct = new ContentTypeObject('frontpage');
        $ct->mainLanguageCode = 'eng-GB';
        $ct->addContentTypeGroup('Content');
        $ct->nameSchema = '<name>';
        $ct->urlAliasSchema = '<name>';
        $ct->isContainer = true;
        $ct->setNames(array('eng-GB' => 'Frontpage'));
        $ct->setDescriptions(array('eng-GB' => 'A frontpage'));

        $field = new FieldDefinitionObject('name');
        $field->type = 'ezstring';
        $field->setNames(array('eng-GB' => 'Name'));
        $field->setDescriptions(array('eng-GB' => 'Name of the frontpage'));
        $field->fieldGroup = 'content';
        $field->position = 10;
        $field->isTranslatable = true;
        $field->isRequired = true;
        $field->isSearchable = true;
        $field->isInfoCollector = false;
        $ct->addFieldDefinition($field);

        $field = new FieldDefinitionObject('description');
        $field->type = 'ezstring';
        $field->setNames(array('eng-GB' => 'Description'));
        $field->setDescriptions(array('eng-GB' => 'Description of the frontpage'));
        $field->fieldGroup = 'content';
        $field->position = 20;
        $field->isTranslatable = true;
        $field->isRequired = false;
        $field->isSearchable = true;
        $field->isInfoCollector = false;
        $ct->addFieldDefinition($field);

        return $ct;
    }

    /**
     * @return ContentTypeObject
     */
    protected function getUpdatedFrontpageContentTypeObject()
    {
        $ct = new ContentTypeObject('frontpage');
        $ct->isContainer = false;
        $ct->setNames(array('eng-GB' => 'Updated frontpage'));
        $ct->setDescriptions(array('eng-GB' => 'Updated frontpage description'));

        $field = new FieldDefinitionObject('name');
        $field->type = 'ezstring';
        $field->setNames(array('eng-GB' => 'Name'));
        $field->setDescriptions(array('eng-GB' => 'Updated name description'));
        $field->fieldGroup = 'content';
        $field->position = 10;
        $field->isTranslatable = false;
        $field->isRequired = false;
        $field->isSearchable = false;
        $field->isInfoCollector = false;
        $ct->addFieldDefinition($field);

        $field = new FieldDefinitionObject('short_description');
        $field->setNames(array('eng-GB' => 'Short description'));
        $ct->addFieldDefinition($field);

        return $ct;
    }

}