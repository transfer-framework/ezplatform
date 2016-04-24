<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\LanguageObject;
use Transfer\EzPlatform\Tests\EzPlatformTestCase;

/**
 * Language manager tests.
 */
class LanguageManagerTest extends EzPlatformTestCase
{
    public function testEnableLanguage()
    {
        $manager = static::$languageManager;
        $engGB = new LanguageObject(array('code' => 'eng-GB'));
        $language = $manager->create($engGB);
        $this->assertInstanceOf(LanguageObject::class, $language);
        $this->assertEquals('eng-GB', $language->data['code']);
        $this->assertTrue($language->data['enabled']);
    }

    public function testCreateLanguage()
    {
        $manager = static::$languageManager;
        $sweSE = new LanguageObject(array('code' => 'swe-SE'));
        $language = $manager->create($sweSE);

        $this->assertInstanceOf(LanguageObject::class, $language);
        $this->assertEquals('swe-SE', $language->data['code']);
        $this->assertTrue($language->data['enabled']);
    }

    public function testUpdateLanguage()
    {
        $manager = static::$languageManager;
        $engGB = new LanguageObject(array('code' => 'eng-GB'));
        $engGB->data['name'] = 'New name';
        $language = $manager->update($engGB);
        $this->assertEquals('New name', $language->data['name']);
    }

    public function testCreateOrUpdate()
    {
        $code = 'rus-RU';
        $manager = static::$languageManager;
        $rusRU = new LanguageObject(array('code' => $code));
        /** @var LanguageObject $language */
        $language = $manager->createOrUpdate($rusRU);
        $this->assertEquals($code, $language->data['code']);
        $language = $manager->createOrUpdate($rusRU);
        $this->assertEquals($code, $language->data['code']);
    }

    public function testRemove()
    {
        $manager = static::$languageManager;
        $gerDE = new LanguageObject(array('code' => 'ger-DE'));
        $manager->createOrUpdate($gerDE);
        $manager->remove($gerDE);
        $this->assertFalse($manager->find($gerDE));
    }

    public function testRemoveNotFound()
    {
        $manager = static::$languageManager;
        $object = new LanguageObject(array('code' => 'ita-IT'));
        $this->assertTrue($manager->remove($object));
    }

    public function testRemoveMainLanguage()
    {
        $manager = static::$languageManager;
        $object = new LanguageObject(array('code' => 'eng-GB'));
        $this->assertFalse($manager->remove($object));
    }
}
