<?php

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\Repository\Values\FieldDefinitionObject;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class ArrayToEzPlatformContentTypeObjectTest extends \PHPUnit_Framework_TestCase
{
    public function testEmptyField()
    {
        $array = array(array(
            'identifier' => 'article',
            'fields' => array(
                'title' => null,
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
        $array = array('contenttypes' => array(
            array(
                'identifier' => 'article',
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
            ),
            array(
                'identifier' => 'frontpage',
                'names' => array(
                    'eng-GB' => 'Frontpage',
                    'nor-NO' => 'Forside',
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
            ),
        ));

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);
        $ct = $ct[0];

        $this->assertArrayHasKey('eng-GB', $ct->data['names']);
        $this->assertEquals('Article', $ct->data['names']['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $ct->data['descriptions']);
        $this->assertEquals('Article description', $ct->data['descriptions']['eng-GB']);
        $this->assertArrayHasKey('nor-NO', $ct->data['names']);
        $this->assertEquals('Artikkel', $ct->data['names']['nor-NO']);
        $this->assertArrayHasKey('nor-NO', $ct->data['descriptions']);
        $this->assertEquals('Artikkelbeskrivelse', $ct->data['descriptions']['nor-NO']);

        $f1 = $ct->data['fields']['title'];
        $this->assertArrayHasKey('eng-GB', $f1->data['names']);
        $this->assertEquals('Title', $f1->data['names']['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f1->data['descriptions']);
        $this->assertEquals('Title of the article', $f1->data['descriptions']['eng-GB']);
        $this->assertArrayHasKey('nor-NO', $f1->data['names']);
        $this->assertEquals('Tittel', $f1->data['names']['nor-NO']);
        $this->assertArrayHasKey('nor-NO', $f1->data['descriptions']);
        $this->assertEquals('Artikkelens tittel', $f1->data['descriptions']['nor-NO']);
    }

    public function testFull()
    {
        $array = $this->getDetailedArrayExample();

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);

        $ct0 = $ct[0];

        $this->assertInstanceOf(ContentTypeObject::class, $ct0);
        $this->assertEquals('article', $ct0->data['identifier']);
        $this->assertEquals('Content', $ct0->data['contenttype_groups'][0]);
        $this->assertArrayHasKey('eng-GB', $ct0->data['names']);
        $this->assertEquals('Article', $ct0->data['names']['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $ct0->data['descriptions']);
        $this->assertEquals('Article description', $ct0->data['descriptions']['eng-GB']);
        $this->assertEquals('<title>', $ct0->data['name_schema']);
        $this->assertEquals('<title>', $ct0->data['url_alias_schema']);
        $this->assertTrue($ct0->data['is_container']);
        $this->assertTrue($ct0->data['default_always_available']);
        $this->assertEquals(Location::SORT_FIELD_PUBLISHED, $ct0->data['default_sort_field']);
        $this->assertEquals(Location::SORT_ORDER_ASC, $ct0->data['default_sort_order']);

        $this->assertCount(2, $ct0->data['fields']);
        $f0 = $ct0->data['fields']['title'];
        $this->assertInstanceOf(FieldDefinitionObject::class, $f0);
        $this->assertEquals('title', $f0->data['identifier']);
        $this->assertEquals('ezstring', $f0->data['type']);
        $this->assertEquals('content', $f0->data['field_group']);
        $this->assertEquals(0, $f0->data['position']);
        $this->assertArrayHasKey('eng-GB', $f0->data['names']);
        $this->assertEquals('Title', $f0->data['names']['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f0->data['descriptions']);
        $this->assertEquals('Title of the article', $f0->data['descriptions']['eng-GB']);
        $this->assertEquals('My Article', $f0->data['default_value']);
        $this->assertTrue($f0->data['is_required']);
        $this->assertTrue($f0->data['is_translatable']);
        $this->assertTrue($f0->data['is_searchable']);
        $this->assertFalse($f0->data['is_info_collector']);

        $f1 = $ct0->data['fields']['content'];
        $this->assertInstanceOf(FieldDefinitionObject::class, $f1);
        $this->assertEquals('content', $f1->data['identifier']);
        $this->assertEquals('ezrichtext', $f1->data['type']);
        $this->assertEquals('content', $f1->data['field_group']);
        $this->assertEquals(10, $f1->data['position']);

        $this->assertArrayHasKey('eng-GB', $f1->data['names']);
        $this->assertEquals('Content', $f1->data['names']['eng-GB']);
        $this->assertArrayHasKey('eng-GB', $f1->data['descriptions']);
        $this->assertEquals('Content of the article', $f1->data['descriptions']['eng-GB']);
        $this->assertEquals('', $f1->data['default_value']);
        $this->assertFalse($f1->data['is_required']);
        $this->assertTrue($f1->data['is_translatable']);
        $this->assertTrue($f1->data['is_searchable']);
        $this->assertFalse($f1->data['is_info_collector']);
    }

    public function testMini()
    {
        $identifier = 'contenttype_mini';
        $array = $this->getMiniArrayExample($identifier);

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($array);

        reset($ct);
        $ct0 = current($ct);

        $this->assertInstanceOf(ContentTypeObject::class, $ct0);
        $this->assertEquals($identifier, $ct0->data['identifier']);
        $this->assertCount(1, $ct0->data['contenttype_groups']);
        $this->assertEquals('Content', $ct0->data['contenttype_groups'][0]);
        $this->assertArrayHasKey('eng-GB', $ct0->data['names']);
        $this->assertEquals('Contenttype Mini', $ct0->data['names']['eng-GB']);
        $this->assertCount(0, $ct0->data['descriptions']);
        $this->assertEquals('<title>', $ct0->data['name_schema']);
        $this->assertEquals('<title>', $ct0->data['url_alias_schema']);
        $this->assertTrue($ct0->data['is_container']);
        $this->assertFalse($ct0->data['default_always_available']);
        $this->assertEquals(Location::SORT_FIELD_NAME, $ct0->data['default_sort_field']);
        $this->assertEquals(Location::SORT_ORDER_ASC, $ct0->data['default_sort_order']);

        $this->assertCount(2, $ct0->data['fields']);

        $f0 = $ct0->data['fields']['title'];

        $this->assertInstanceOf(FieldDefinitionObject::class, $f0);
        $this->assertEquals('title', $f0->data['identifier']);
        $this->assertEquals('ezstring', $f0->data['type']);
        $this->assertEquals('content', $f0->data['field_group']);
        $this->assertEquals(10, $f0->data['position']);
        $this->assertArrayHasKey('eng-GB', $f0->data['names']);
        $this->assertEquals('Title', $f0->data['names']['eng-GB']);
        $this->assertCount(0, $f0->data['descriptions']);
        $this->assertNull($f0->data['default_value']);
        $this->assertFalse($f0->data['is_required']);
        $this->assertTrue($f0->data['is_translatable']);
        $this->assertTrue($f0->data['is_searchable']);
        $this->assertFalse($f0->data['is_info_collector']);

        $f1 = $ct0->data['fields']['content'];
        $this->assertInstanceOf(FieldDefinitionObject::class, $f1);
        $this->assertEquals('content', $f1->data['identifier']);
        $this->assertEquals('ezstring', $f1->data['type']);
        $this->assertEquals('content', $f1->data['field_group']);
        $this->assertEquals(10, $f1->data['position']);
        $this->assertArrayHasKey('eng-GB', $f1->data['names']);
        $this->assertEquals('Content', $f1->data['names']['eng-GB']);
        $this->assertCount(0, $f1->data['descriptions']);
        $this->assertNull($f1->data['default_value']);
        $this->assertFalse($f1->data['is_required']);
        $this->assertTrue($f1->data['is_translatable']);
        $this->assertTrue($f1->data['is_searchable']);
        $this->assertFalse($f1->data['is_info_collector']);
    }

    public function testIdentifierAsKey()
    {
        $identifier = 'ct_id_as_key';
        $contentTypeArray = [$identifier => [
            'fields' => [
                'title' => [
                    'type' => 'ezstring',
                ],
                'content' => [
                    'type' => 'ezstring',
                ],
            ],
        ]];

        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $ct = $transformer->handle($contentTypeArray);

        reset($ct);
        $ct0 = current($ct);

        $this->assertInstanceOf(ContentTypeObject::class, $ct0);
        $this->assertEquals($identifier, $ct0->data['identifier']);
    }

    protected function getMiniArrayExample($identifier = 'article')
    {
        return [[
            'identifier' => $identifier,
            'fields' => [
                'title' => [
                    'type' => 'ezstring',
                ],
                'content' => [
                    'type' => 'ezstring',
                ],
            ],
        ]];
    }

    protected function getDetailedArrayExample()
    {
        return array(array(
            'identifier' => 'article',
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => 'Content',
            'names' => 'Article',
            'descriptions' => 'Article description',
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
                    'names' => 'Title',
                    'descriptions' => 'Title of the article',
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
