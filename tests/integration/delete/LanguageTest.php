<?php

namespace Transfer\EzPlatform\tests\integration\delete;

use eZ\Publish\Core\Base\Exceptions\NotFoundException;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;
use Transfer\EzPlatform\tests\testcase\LanguageTestCase;

class LanguageTest extends LanguageTestCase
{
    public function testDelete()
    {
        $code = 'swe-SE';
        $raw = $this->getLanguage($code);

        // Create it so we know it exists.
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentLanguageService()->loadLanguage($code);
        $this->assertTrue($real->enabled);

        // Mark with DELETE, and delete it.
        $raw->setProperty('action', Action::DELETE);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $this->setExpectedException(NotFoundException::class);
        static::$repository->getContentLanguageService()->loadLanguage($code);
    }
}
