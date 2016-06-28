<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\testcase;

use Psr\Log\LoggerInterface;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;

class EzPlatformAdapterTestCase extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    public $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(static::$repository);
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }
}
