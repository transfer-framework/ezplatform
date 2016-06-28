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
use Transfer\EzPlatform\Repository\Values\LanguageObject;

class LanguageTestCase extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    public $adapter;

    public function setUp()
    {
        parent::setUp();
        $this->adapter = new EzPlatformAdapter(static::$repository);
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }

    protected function getLanguage($code)
    {
        return new LanguageObject(array(
            'code' => $code,
        ));
    }
}
