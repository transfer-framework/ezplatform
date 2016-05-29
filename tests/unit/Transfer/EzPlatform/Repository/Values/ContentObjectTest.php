<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Values;

use Transfer\EzPlatform\Exception\InvalidDataStructureException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;

/**
 * Content manager unit tests.
 */
class ContentObjectTest extends ContentTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testAddInvalidParentLocation()
    {
        $this->setExpectedException(InvalidDataStructureException::class);

        $contentObject = new ContentObject([]);
        $contentObject->addParentLocation([]);
    }
}
