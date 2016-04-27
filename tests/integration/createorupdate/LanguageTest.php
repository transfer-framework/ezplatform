<?php

namespace Transfer\EzPlatform\tests\integration;

use Psr\Log\LoggerInterface;
use Transfer\Adapter\Transaction\Request;
use Transfer\EzPlatform\Adapter\EzPlatformAdapter;
use Transfer\EzPlatform\Data\LanguageObject;
use Transfer\EzPlatform\tests\testcase\EzPlatformTestCase;

class LanguageTest extends EzPlatformTestCase
{
    /**
     * @var EzPlatformAdapter
     */
    protected $adapter;

    public function setUp()
    {
        $this->adapter = new EzPlatformAdapter(array(
            'repository' => static::$repository,
        ));
        $this->adapter->setLogger(
            $this->getMock(LoggerInterface::class)
        );
    }

    public function testCreateAndUpdateLanguage()
    {
        $code = 'chi-CN';
        $raw = $this->getLanguage($code);
        $this->adapter->send(new Request(array(
            $raw,
        )));

        $real = static::$repository->getContentLanguageService()->loadLanguage($code);
        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Language', $real);
        $this->assertEquals('Simplified Chinese', $real->name);

        $raw = $this->getLanguage($code);
        $raw->data['name'] = 'Advanced Chinese';
        $this->adapter->send(new Request(array(
            $raw,
        )));
        $real = static::$repository->getContentLanguageService()->loadLanguage($code);
        $this->assertInstanceOf('\eZ\Publish\API\Repository\Values\Content\Language', $real);
        $this->assertEquals('Advanced Chinese', $real->name);
    }

    protected function getLanguage($code)
    {
        return new LanguageObject(array(
            'code' => $code,
        ));
    }
}
