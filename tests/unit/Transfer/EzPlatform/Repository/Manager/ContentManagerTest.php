<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\Repository\Values\ContentObject;
use Transfer\EzPlatform\tests\testcase\ContentTestCase;

/**
 * Content manager unit tests.
 */
class ContentManagerTest extends ContentTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFindNotFoundException()
    {
        $this->setExpectedException(NotFoundException::class);

        static::$contentManager->find(
            new ValueObject([], [
                'remote_id' => 'i_dont_exist_321123',
            ]),
            true
        );
    }

    public function testRemoveNotFound()
    {
        $this->assertFalse(
            static::$contentManager->remove(
                new ContentObject([], [
                    'remote_id' => 'i_dont_exist_321123',
                ])
            )
        );
    }

    public function testInvalidClassOnCreate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentManager->create($object);
    }

    public function testInvalidClassOnUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentManager->update($object);
    }

    public function testInvalidClassOnCreateOrUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentManager->createOrUpdate($object);
    }

    public function testInvalidClassOnDelete()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$contentManager->remove($object);
    }
}
