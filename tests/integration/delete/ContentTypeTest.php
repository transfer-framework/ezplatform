<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;

class ContentTypeTest extends ContentTypeTestCase
{
    public function testDelete()
    {
        $identifier = 'integration_contenttype_delete_test';

        $raw = $this->getContentTypeFull($identifier);

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
