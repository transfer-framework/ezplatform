<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;

class ContentTypeTest extends ContentTypeTestCase
{
    public function testCreateAndUpdateContentType()
    {
        $identifier = 'product';

        $contentObjectData = $this->getContentTypeMini($identifier);
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
}
