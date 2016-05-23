<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use eZ\Publish\API\Repository\Values\Content\Location;
use Psr\Log\AbstractLogger;
use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\Data\TreeObject;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\Repository\Values\ContentTypeObject;
use Transfer\EzPlatform\Repository\Values\EzPlatformObject;
use Transfer\EzPlatform\Repository\Values\UserGroupObject;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;
use Transfer\EzPlatform\Worker\Transformer\ArrayToEzPlatformContentTypeObjectTransformer;
use Transfer\Adapter\Transaction\Response;

class EzPlatformAdapterTest extends EzPlatformTestCase
{
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
    }

    public function testSendSkipAction()
    {
        $object = new UserObject([]);
        $object->setProperty('action', Action::SKIP);

        $response = $this->adapter->send(new Request(array(
            $object,
        )));

        $this->assertNull($response->getData()[0]);
    }
}
