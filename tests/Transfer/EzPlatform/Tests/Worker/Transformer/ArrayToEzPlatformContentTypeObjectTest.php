<?php

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class ArrayToEzPlatformContentTypeObjectTest extends \PHPUnit_Framework_TestCase
{

    public function testEmptyField()
    {
        $array = array('article' => array(
            'fields' => array(
                'title' => '',
            ),
        ));

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $transformer->handle($array);
    }

    public function testEmptyArray()
    {
        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $this->assertNull($transformer->handle(array()));
    }

    public function testMultilingual()
    {
        $array = array('article' => array(
            'names' => array(
                'eng-GB' => 'Article',
                'nor-NO' => 'Artikkel',
            ),
            'descriptions' => array(
                'eng-GB' => 'Article description',
                'nor-NO' => 'Artikkelbeskrivelse',
            ),
            'fields' => array(
                'title' => array(
                    'type' => 'ezstring',
                    'names' => array(
                        'eng-GB' => 'Title',
                        'nor-NO' => 'Tittel',
                    ),
                    'descriptions' => array(
                        'eng-GB' => 'Title of the article',
                        'nor-NO' => 'Artikkelens tittel',
                    ),
                ),
            ),
        ));

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);

        $this->assertArrayHasKey('eng-GB', $ct->getNames());
        $this->assertEquals('Article', $ct->getNames()['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $ct->getDescriptions());
        $this->assertEquals('Article description', $ct->getDescriptions()['eng-GB']);
        $this->assertArrayHasKey('nor-NO', $ct->getNames());
        $this->assertEquals('Artikkel', $ct->getNames()['nor-NO']);
        $this->assertArrayHasKey('nor-NO', $ct->getDescriptions());
        $this->assertEquals('Artikkelbeskrivelse', $ct->getDescriptions()['nor-NO']);

        $f1 = $ct->getFieldDefinitions()[0];
        $this->assertArrayHasKey('eng-GB', $f1->getNames());
        $this->assertEquals('Title', $f1->getNames()['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f1->getDescriptions());
        $this->assertEquals('Title of the article', $f1->getDescriptions()['eng-GB']);
        $this->assertArrayHasKey('nor-NO', $f1->getNames());
        $this->assertEquals('Tittel', $f1->getNames()['nor-NO']);
        $this->assertArrayHasKey('nor-NO', $f1->getDescriptions());
        $this->assertEquals('Artikkelens tittel', $f1->getDescriptions()['nor-NO']);
    }

    public function testInvalidIdentifier()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\InvalidDataStructureException');

        $array = array(array(
            'fields' => array(
                'title' => array(),
                'content' => array(),
            ),
        ));

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $transformer->handle($array);
    }

    public function testInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');
        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $transformer->handle('a string');
    }

    public function testFull()
    {
        $array = $this->getDetailedArrayExample();

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);

        $this->assertInstanceOf('Transfer\EzPlatform\Data\ContentTypeObject', $ct);
        $this->assertEquals('article', $ct->getIdentifier());
        $this->assertEquals('Content', $ct->getMainGroupIdentifier());
        $this->assertCount(2, $ct->getContentTypeGroups());
        $this->assertEquals('Content', $ct->getContentTypeGroups()[0]);
        $this->assertEquals('Full', $ct->getContentTypeGroups()[1]);
        $this->assertArrayHasKey('eng-GB', $ct->getNames());
        $this->assertEquals('Article', $ct->getNames()['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $ct->getDescriptions());
        $this->assertEquals('Article description', $ct->getDescriptions()['eng-GB']);
        $this->assertEquals('<title>', $ct->nameSchema);
        $this->assertEquals('<title>', $ct->urlAliasSchema);
        $this->assertTrue($ct->isContainer);
        $this->assertTrue($ct->defaultAlwaysAvailable);
        $this->assertEquals(Location::SORT_FIELD_NAME, $ct->defaultSortField);
        $this->assertEquals(Location::SORT_ORDER_ASC, $ct->defaultSortOrder);

        $this->assertCount(2, $ct->getFieldDefinitions());
        $f1 = $ct->getFieldDefinitions()[0];
        $this->assertInstanceOf('Transfer\EzPlatform\Data\FieldDefinitionObject', $f1);
        $this->assertEquals('title', $f1->getIdentifier());
        $this->assertEquals('ezstring', $f1->type);
        $this->assertEquals('content', $f1->fieldGroup);
        $this->assertEquals(0, $f1->position);
        $this->assertArrayHasKey('eng-GB', $f1->getNames());
        $this->assertEquals('Title', $f1->getNames()['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f1->getDescriptions());
        $this->assertEquals('Title of the article', $f1->getDescriptions()['eng-GB']);
        $this->assertEquals('My Article', $f1->defaultValue);
        $this->assertTrue($f1->isRequired);
        $this->assertTrue($f1->isTranslatable);
        $this->assertTrue($f1->isSearchable);
        $this->assertFalse($f1->isInfoCollector);

        $this->assertCount(2, $ct->getFieldDefinitions());
        $f2 = $ct->getFieldDefinitions()[1];
        $this->assertInstanceOf('Transfer\EzPlatform\Data\FieldDefinitionObject', $f2);
        $this->assertEquals('content', $f2->getIdentifier());
        $this->assertEquals('ezrichtext', $f2->type);
        $this->assertEquals('content', $f2->fieldGroup);
        $this->assertEquals(10, $f2->position);
        $this->assertArrayHasKey('eng-GB', $f2->getNames());
        $this->assertEquals('Content', $f2->getNames()['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f2->getDescriptions());
        $this->assertEquals('Content of the article', $f2->getDescriptions()['eng-GB']);
        $this->assertEquals('', $f2->defaultValue);
        $this->assertFalse($f2->isRequired);
        $this->assertTrue($f2->isTranslatable);
        $this->assertTrue($f2->isSearchable);
        $this->assertFalse($f2->isInfoCollector);
    }

    public function testMini()
    {
        $array = $this->getMiniArrayExample();

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);

        $this->assertInstanceOf('Transfer\EzPlatform\Data\ContentTypeObject', $ct);
        $this->assertEquals('article', $ct->getIdentifier());
        $this->assertEquals('Content', $ct->getMainGroupIdentifier());
        $this->assertCount(1, $ct->getContentTypeGroups());
        $this->assertEquals('Content', $ct->getContentTypeGroups()[0]);
        $this->assertArrayHasKey('eng-GB', $ct->getNames());
        $this->assertEquals('Article', $ct->getNames()['eng-GB']);
        $this->assertCount(0, $ct->getDescriptions());
        $this->assertEquals('<title>', $ct->nameSchema);
        $this->assertEquals('<title>', $ct->urlAliasSchema);
        $this->assertTrue($ct->isContainer);
        $this->assertTrue($ct->defaultAlwaysAvailable);
        $this->assertEquals(Location::SORT_FIELD_NAME, $ct->defaultSortField);
        $this->assertEquals(Location::SORT_ORDER_ASC, $ct->defaultSortOrder);

        $this->assertCount(2, $ct->getFieldDefinitions());
        $f1 = $ct->getFieldDefinitions()[0];
        $this->assertInstanceOf('Transfer\EzPlatform\Data\FieldDefinitionObject', $f1);
        $this->assertEquals('title', $f1->getIdentifier());
        $this->assertEquals('ezstring', $f1->type);
        $this->assertEquals('content', $f1->fieldGroup);
        $this->assertEquals(10, $f1->position);
        $this->assertArrayHasKey('eng-GB', $f1->getNames());
        $this->assertEquals('Title', $f1->getNames()['eng-GB']);
        $this->assertCount(0, $f1->getDescriptions());
        $this->assertNull($f1->defaultValue);
        $this->assertFalse($f1->isRequired);
        $this->assertTrue($f1->isTranslatable);
        $this->assertTrue($f1->isSearchable);
        $this->assertFalse($f1->isInfoCollector);

        $this->assertCount(2, $ct->getFieldDefinitions());
        $f2 = $ct->getFieldDefinitions()[1];
        $this->assertInstanceOf('Transfer\EzPlatform\Data\FieldDefinitionObject', $f2);
        $this->assertEquals('content', $f2->getIdentifier());
        $this->assertEquals('ezstring', $f2->type);
        $this->assertEquals('content', $f2->fieldGroup);
        $this->assertEquals(20, $f2->position);
        $this->assertArrayHasKey('eng-GB', $f2->getNames());
        $this->assertEquals('Content', $f2->getNames()['eng-GB']);
        $this->assertCount(0, $f2->getDescriptions());
        $this->assertNull($f2->defaultValue);
        $this->assertFalse($f2->isRequired);
        $this->assertTrue($f2->isTranslatable);
        $this->assertTrue($f2->isSearchable);
        $this->assertFalse($f2->isInfoCollector);
    }

    protected function getMiniArrayExample()
    {
        return array('article' => array(
            'fields' => array(
                'title' => array(),
                'content' => array(),
            ),
        ));
    }

    protected function getDetailedArrayExample()
    {
        return array('article' => array(
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'contenttype_group' => 'Full',
            'name' => 'Article',
            'description' => 'Article description',
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'is_container' => true,
            'default_always_available' => true,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'title' => array(
                    'type' => 'ezstring',
                    'field_group' => 'content',
                    'position' => 0,
                    'name' => 'Title',
                    'description' => 'Title of the article',
                    'default_value' => 'My Article',
                    'is_required' => true,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
                'content' => array(
                    'type' => 'ezrichtext',
                    'field_group' => 'content',
                    'position' => 10,
                    'names' => array('eng-GB' => 'Content'),
                    'descriptions' => array('eng-GB' => 'Content of the article'),
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
