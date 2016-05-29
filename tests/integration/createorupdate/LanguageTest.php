<?php

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Language;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\tests\testcase\LanguageTestCase;

class LanguageTest extends LanguageTestCase
{
    public function testCreateAndUpdateLanguage()
    {
        $code = 'chi-CN';
        $raw = $this->getLanguage($code);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentLanguageService()->loadLanguage($code);
        $this->assertInstanceOf(Language::class, $real);
        $this->assertEquals('Simplified Chinese', $real->name);

        $raw = $this->getLanguage($code);
        $raw->data['name'] = 'Advanced Chinese';

        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentLanguageService()->loadLanguage($code);
        $this->assertInstanceOf(Language::class, $real);
        $this->assertEquals('Advanced Chinese', $real->name);
    }
}
