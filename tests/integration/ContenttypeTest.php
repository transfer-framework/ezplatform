<?php

namespace Transfer\EzPlatform\tests\integration;

use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentTypeObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class ContentTypeTest extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
    }

    public function testCreateAndUpdateContentType()
    {
        $identifier = '_product';

        $raw = $this->getContentType($identifier);

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\ContentType\ContentType', $real);
        $this->assertEquals('Product', $real->getName('eng-GB'));

        $raw = $this->getContentType($identifier);
        $raw->data['names']['eng-GB'] = 'Updated name';

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\ContentType\ContentType', $real);
        $this->assertEquals('Updated name', $real->getName('eng-GB'));
    }

    protected function getContentType($identifier)
    {
        return new ContentTypeObject($identifier, array(
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'names' => array('eng-GB' => 'Product'),
            'descriptions' => array('eng-GB' => 'Product description'),
            'is_container' => true,
            'default_always_available' => false,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'name' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Name'),
                    'descriptions' => array('eng-GB' => 'Name of the contenttype'),
                    'field_group' => 'content',
                    'position' => 10,
                    'is_required' => true,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
                'description' => array(
                    'type' => 'ezrichtext',
                    'names' => array('eng-GB' => 'Description'),
                    'descriptions' => array('eng-GB' => 'Description of the contenttype'),
                    'field_group' => 'content',
                    'position' => 20,
                    'is_required' => false,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,

                ),
            ),
        ));
    }
}
