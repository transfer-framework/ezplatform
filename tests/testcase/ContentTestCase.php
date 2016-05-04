<?php

namespace Transfer\EzPlatform\tests\testcase;

use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;

class ContentTestCase extends EzPlatformTestCase
{
    const _content_type_article = '_test_ct_article';
    const _content_type_folder = '_test_ct_folder';

    /**
     * @var EzPlatformAdapter
     */
    public $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );

        // requesites
        $this->setUpContentTypes();
    }

    /**
     * @param array         $fields
     * @param string        $remoteId
     * @param string        $contentTypeIdentifier
     * @param bool          $languageCode
     * @param bool|array    $parentLocations
     *
     * @return ContentObject
     */
    public function getContentObject(array $fields, $remoteId, $contentTypeIdentifier, $languageCode = false, $parentLocations = false)
    {
        $co = new ContentObject(
            $fields,
            array(
                'remote_id' => $remoteId,
                'content_type_identifier' => $contentTypeIdentifier,
                'language' => $languageCode ?: 'eng-GB',
            )
        );

        if ($parentLocations) {
            $co->setParentLocations($parentLocations);
        }

        return $co;
    }

    /**
     * @param $content
     *
     * @return string
     */
    protected function getRichtext($content)
    {
        return sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
<section xmlns="http://docbook.org/ns/docbook" xmlns:xlink="http://www.w3.org/1999/xlink" version="5.0-variant ezpublish-1.0">%s</section>
',
            $content);
    }

    protected function setUpContentTypes()
    {
        $_ct_article = static::getContentTypeObject(static::_content_type_article);
        static::$contentTypeManager->createOrUpdate($_ct_article);
    }

    /** @todo move to ContentTypeTestCase ? */
    public static function getContentTypeObject($identifier)
    {
        return new ContentTypeObject(array(
            'identifier' => $identifier,
            'main_language_code' => 'eng-GB',
            'contenttype_groups' => array('Content'),
            'name_schema' => '<title>',
            'url_alias_schema' => '<title>',
            'names' => array('eng-GB' => 'Article'),
            'descriptions' => array('eng-GB' => 'An article'),
            'is_container' => true,
            'default_always_available' => false,
            'default_sort_field' => Location::SORT_FIELD_PUBLISHED,
            'default_sort_order' => Location::SORT_ORDER_ASC,
            'fields' => array(
                'title' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Title'),
                    'descriptions' => array('eng-GB' => 'Title of the article'),
                    'field_group' => 'content',
                    'position' => 10,
                    'is_required' => true,
                    'is_translatable' => true,
                    'is_searchable' => true,
                    'is_info_collector' => false,
                ),
                'description' => array(
                    'type' => 'ezstring',
                    'names' => array('eng-GB' => 'Description'),
                    'descriptions' => array('eng-GB' => 'Description of the article'),
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
