<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Values;

use Transfer\EzPlatform\Exception\LanguageNotFoundException;
use Transfer\EzPlatform\Repository\Values\LanguageObject;
use Transfer\EzPlatform\tests\testcase\LanguageTestCase;

class LanguageObjectTest extends LanguageTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testUnknownLanguage()
    {
        $this->setExpectedException(LanguageNotFoundException::class);

        new LanguageObject(array(
            'code' => 'a_language_code_without_a_known_name',
        ));
    }
}
