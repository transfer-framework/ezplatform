<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
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

    public function testFindNotFoundException()
    {
        $this->setExpectedException(NotFoundException::class);

        static::$contentTypeManager->find(
            new ValueObject([
                'identifier' => 'i_dont_exist_321123',
            ]),
            true
        );
    }

    public function testRemoveNotFound()
    {
        $contentTypeObject = $this->getContentTypeFull('i_dont_exist_321123');

        $this->assertFalse(
            static::$contentTypeManager->remove($contentTypeObject)
        );
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
