<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Values\ContentObject;

class ContentTest extends ContentTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    /**
     * Tests content creation.
     */
    public function testCreateContent()
    {
        $remoteId = 'test_integration_content_1';

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

    public function testCreateContentWithLocations()
    {
        $remoteId = 'test_integration_content_with_locations_1';
        $parentNodeId = 2;

        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $remoteId, static::_content_type_article, false,
            array(
                $parentNodeId,
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($remoteId);
        $locations = static::$repository->getLocationService()->loadLocations($content->contentInfo);

        $this->assertCount(1, $locations);

        /** @var Location $location */
        $location = current($locations);
        $this->assertEquals($parentNodeId, $location->parentLocationId);
    }

    public function testUpdateContentWithLocations()
    {
        $remoteId = 'test_integration_content_with_locations_1';

        // Media root folder
        $parentNodeId = 43;

        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $remoteId, static::_content_type_article, false,
            array(
                $parentNodeId,
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($remoteId);
        $locations = static::$repository->getLocationService()->loadLocations($content->contentInfo);

        $this->assertCount(1, $locations);

        /** @var Location $location */
        $location = current($locations);
        $this->assertEquals($parentNodeId, $location->parentLocationId);
    }

    /**
     * Tests content update.
     */
    public function testUpdateContent()
    {
        $remoteId = 'test_integration_content_1';

        $contentObject = new ContentObject(
            array(
                'title' => 'Test updated title',
            ),
            array(
                'language' => 'eng-GB',
                'content_type_identifier' => '_test_article',
                'remote_id' => $remoteId,
            )
        );

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($remoteId);

        $this->assertInstanceOf(Content::class, $content);
        $this->assertEquals('Test updated title', $content->getField('title')->value->text);
        $this->assertEquals('eng-GB', $content->contentInfo->mainLanguageCode);
        $this->assertEquals(36, $content->contentInfo->contentTypeId);
    }

    /**
     * Tests InvalidArgumentException.
     */
    public function testCreateOrUpdateWithInvalidArgument()
    {
        $this->setExpectedException(\InvalidArgumentException::class);

        $this->adapter->send(new Request(array(
            new ValueObject(null),
        )));
    }

    /**
     * Tests content struct callback.
     */
    public function testStructCallback()
    {
        $remoteId = 'test_integration_content_3';

        $contentObject = $this->getContentObject(array(
            'title' => 'Test title',
        ), $remoteId, static::_content_type_article);

        $contentObject->setProperty('struct_callback', function (ContentCreateStruct $struct) {
            $struct->sectionId = 44;
            $struct->ownerId = 12;
        });

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $content = static::$repository->getContentService()->loadContentByRemoteId($remoteId);

        $this->assertEquals(44, $content->contentInfo->sectionId);
        $this->assertEquals(12, $content->contentInfo->ownerId);
    }
}
