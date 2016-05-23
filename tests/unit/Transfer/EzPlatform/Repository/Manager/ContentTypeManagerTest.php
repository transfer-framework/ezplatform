<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\tests\testcase\ContentTypeTestCase;

/**
 * Contenttype manager unit tests.
 */
class ContentTypeManagerTest extends ContentTypeTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testInvalidClassOnCreate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentTypeManager->create($object);
    }

    public function testInvalidClassOnUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentTypeManager->update($object);
    }

    public function testInvalidClassOnCreateOrUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentTypeManager->createOrUpdate($object);
    }

    public function testInvalidClassOnDelete()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentTypeManager->remove($object);
    }

}
