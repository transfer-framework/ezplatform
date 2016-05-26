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
use Transfer\EzPlatform\tests\testcase\UserGroupTestCase;

/**
 * UserGroup manager unit tests.
 */
class UserGroupManagerTest extends UserGroupTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFindNotFoundException()
    {
        $this->setExpectedException(NotFoundException::class);

        static::$userGroupManager->find(
            new ValueObject([
                'remote_id' => 'i_dont_exist_321123',
            ]),
            true
        );
    }

    public function testInvalidClassOnCreate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userGroupManager->create($object);
    }

    public function testInvalidClassOnUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userGroupManager->update($object);
    }

    public function testInvalidClassOnCreateOrUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userGroupManager->createOrUpdate($object);
    }

    public function testInvalidClassOnDelete()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userGroupManager->remove($object);
    }
}
