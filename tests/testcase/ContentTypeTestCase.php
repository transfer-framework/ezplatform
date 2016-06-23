<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;

class ContentTypeTestCase extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(static::$repository);
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }

    protected function getContentTypeMini($identifier)
    {
        return new ContentTypeObject(array(
            'identifier' => $identifier,
            'fields' => array(
                'name' => array(
                    'type' => 'ezstring',
                    'position' => 10,
                ),
                'content' => array(
                    'type' => 'ezstring',
                    'position' => 20,
                ),
            ),
        ));
    }

    protected function getContentTypeFull($identifier)
    {
        return new ContentTypeObject(array(
            'identifier' => $identifier,
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content', 'My new group'),
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
