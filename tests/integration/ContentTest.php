<?php

namespace Transfer\EzPlatform\tests\integration;

use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\ContentObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

class ContentTest extends EzPlatformTestCase
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
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }

    /**
     * Tests content creation.
     */
    public function testCreateContent()
    {
        $contentObject = new ContentObject(
            array(
                'title' => 'Test title',
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
