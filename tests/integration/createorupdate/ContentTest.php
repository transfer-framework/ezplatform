<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Content;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\tests\ContentTestCase;

class ContentTest extends ContentTestCase
{

    public function setUp() {
        parent::setUp();
    }

    /**
     * Tests content creation.
     */
    public function testCreateContent()
    {
        $remoteId = 'test_article_1';

        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $remoteId, static::_content_type_article);

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($remoteId);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('Test title', $content->contentInfo->name);
        $this->assertEquals('Test title', $content->fields['title']['eng-GB']->text);
        $this->assertEquals('eng-GB', $content->contentInfo->mainLanguageCode);
        $this->assertEquals(36, $content->contentInfo->contentTypeId);
    }

    /**
     * Tests content update.
     */
    public function testUpdateContent()
    {
        $contentObject = new ContentObject(
            array(
                'title' => 'Test updated title',
            ),
            array(
                'language' => 'eng-GB',
                'content_type_identifier' => '_test_article',
                'remote_id' => 'test_article_1',
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId('test_article_1');

        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Content', $content);
        $this->assertEquals('Test updated title', $content->getField('title')->value->text);
        $this->assertEquals('eng-GB', $content->contentInfo->mainLanguageCode);
        $this->assertEquals(36, $content->contentInfo->contentTypeId);
    }

    /**
     * Tests MissingIdentificationPropertyException.
     */
    public function testCreateOrUpdateWithAmbiguousObject()
    {
        $this->setExpectedException('Transfer\EzPlatform\Exception\MissingIdentificationPropertyException');

        $object = new ContentObject(array());

        $this->adapter->send(new Request(array(
            $object,
        )));
    }

    /**
     * Tests InvalidArgumentException.
     */
    public function testCreateOrUpdateWithInvalidArgument()
    {
        $this->setExpectedException('\InvalidArgumentException');

        $this->adapter->send(new Request(array(
            new ValueObject(null),
        )));
    }
}
