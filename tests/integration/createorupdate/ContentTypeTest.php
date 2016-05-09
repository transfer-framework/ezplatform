<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ContentTypeTest extends ContentTypeTestCase
{

    public function testCreateAndUpdateContentType()
    {
        $identifier = '_product';

        $raw = $this->getContentType($identifier);

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Product', $real->getName('eng-GB'));

        $raw = $this->getContentType($identifier);
        $raw->data['names']['eng-GB'] = 'Updated name';

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);
        $this->assertEquals('Updated name', $real->getName('eng-GB'));
    }
}
