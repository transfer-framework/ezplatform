<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\Repository\Manager;

use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\Repository\Values\UserObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformAdapterTestCase;

class EzPlatformAdapterTest extends EzPlatformAdapterTestCase
{
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
