<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\tests\Repository\Manager;

use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

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
