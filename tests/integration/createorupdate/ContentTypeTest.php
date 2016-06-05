<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\ContentTypeCreateStruct;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class ContentTypeTest extends ContentTypeTestCase
{
    public function testCreateAndUpdateContentType()
    {
        $identifier = 'product';

        $contentObjectData = array($this->getContentTypeMiniData($identifier));
        $transformer = new ArrayToEzPlatformContentTypeObjectTransformer();
        $raw = current($transformer->handle($contentObjectData));

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Product', $real->getName('eng-GB'));

        $raw = $this->getContentTypeFull($identifier);
        $raw->data['names']['eng-GB'] = 'Updated name';

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Updated name', $real->getName('eng-GB'));
    }

    public function testDetatchContentGroup()
    {
        $identifier = 'product';

        $raw = $this->getContentTypeFull($identifier);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
        $this->assertCount(2, $real->getContentTypeGroups());

        $raw = $this->getContentTypeFull($identifier);
        $raw->data['contenttype_groups'] = array('Content');
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
        $this->assertCount(1, $real->getContentTypeGroups());
    }

    /**
     * Tests content struct callback.
     */
    public function testStructCallback()
    {
        $remoteId = 'test_integration_contenttype_3';
        $identifier = 'struct_example';

        $contentTypeObject = new ContentTypeObject($this->getContentTypeMiniData($identifier));

        $contentTypeObject->setStructCallback(function (ContentTypeCreateStruct $struct) use ($remoteId) {
            $struct->isContainer = false;
            $struct->defaultSortField = Location::SORT_FIELD_SECTION;
            $struct->remoteId = $remoteId;
        });

        $this->adapter->send(new Request(array(
            $contentTypeObject,
        )));

        $contentType = static::$repository->getContentTypeService()->loadContentTypeByRemoteId($remoteId);

        $this->assertEquals($remoteId, $contentType->remoteId);
        $this->assertEquals(Location::SORT_FIELD_SECTION, $contentType->defaultSortField);
        $this->assertFalse($contentType->isContainer);
    }
}
