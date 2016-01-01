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
    public function testEmptyLanguage()
    {
        $manager = static::$languageManager;
        $emptyValueObject = new ValueObject(array());
        $this->assertNull($manager->create($emptyValueObject));
        $this->assertNull($manager->update($emptyValueObject));
        $this->assertNull($manager->createOrUpdate($emptyValueObject));
        $this->assertNull($manager->remove($emptyValueObject));
    }

    public function testEnableLanguage()
    {
        $manager = static::$languageManager;
        $engGB = new LanguageObject(array('code' => 'eng-GB'));
        $language = $manager->create($engGB)->getData();
        $this->assertInstanceOf('eZ\Publish\API\Repository\Values\Content\Language', $language);
        $this->assertEquals('eng-GB', $language->languageCode);
        $this->assertTrue($language->enabled);
    }

    public function testCreateLanguage()
    {
        $manager = static::$languageManager;
        $sweSE = new LanguageObject(array('code' => 'swe-SE'));
        $language = $manager->create($sweSE)->getData();
        $this->assertInstanceOf('eZ\Publish\API\Repository\Values\Content\Language', $language);
        $this->assertEquals('swe-SE', $language->languageCode);
        $this->assertTrue($language->enabled);
    }

    public function testUpdateLanguage()
    {
        $manager = static::$languageManager;
        $engGB = new LanguageObject(array('code' => 'eng-GB'));
        $engGB->data['name'] = 'New name';
        $manager->update($engGB);
        $language = $manager->findByCode('eng-GB');
        $this->assertEquals('New name', $language->name);
    }

    public function testCreateOrUpdate()
    {
        $code = 'rus-RU';
        $manager = static::$languageManager;
        $gerDE = new LanguageObject(array('code' => $code));
        $manager->createOrUpdate($gerDE);
        $this->assertTrue($manager->exists($code));
        $manager->createOrUpdate($gerDE);
        $this->assertTrue($manager->exists($code));
    }

    public function testRemove()
    {
        $manager = static::$languageManager;
        $gerDE = new LanguageObject(array('code' => 'ger-DE'));
        $manager->createOrUpdate($gerDE);
        $manager->remove($gerDE);
        $this->assertFalse($manager->exists('ger-DE'));
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
