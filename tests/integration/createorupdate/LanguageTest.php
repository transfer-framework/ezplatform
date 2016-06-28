<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\tests\integration\createorupdate;

use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\API\Repository\Values\Content\LanguageCreateStruct;
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

    /**
     * Tests language struct callback.
     */
    public function testStructCallback()
    {
        $code = 'swe-SE';
        $name = 'Svensk';

        $languageObject = $this->getLanguage($code);

        $languageObject->setStructCallback(function (LanguageCreateStruct $struct) use ($name) {
            $struct->name = $name;
        });

        $this->adapter->send(new Request(array(
            $languageObject,
        )));

        $language = static::$repository->getContentLanguageService()->loadLanguage($code);

        $this->assertEquals($name, $language->name);
    }
}
