<?php

namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\API\Repository\Values\Content\Location;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class ContentTypeTest extends ContentTypeTestCase
{

    public function testDelete()
    {
        $identifier = '_integration_contenttype_delete_test';

        $raw = $this->getContentType($identifier);

        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);

        $this->assertInstanceOf(ContentType::class, $real);

        $raw->setProperty('action', Action::DELETE);

        $this->adapter->send(new Request(array(
            $raw,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getContentTypeService()->loadContentTypeByIdentifier($identifier);
    }
}
