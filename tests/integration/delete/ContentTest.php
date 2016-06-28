<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;

class ContentTest extends ContentTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testDelete()
    {
        $content = static::$repository->getContentService()->loadContentByRemoteId('test_integration_content_1');
        $contentObject = new ContentObject([]);
        $contentObject->getMapper()->contentToObject($content);
        $contentObject->setProperty('action', Action::DELETE);

        $this->adapter->send(new Request(array(
            $contentObject,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getContentService()->loadContentByRemoteId('test_integration_content_1');
    }
}
