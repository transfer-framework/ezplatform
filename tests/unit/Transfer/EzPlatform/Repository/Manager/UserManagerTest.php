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
use Transfer\EzPlatform\tests\testcase\UserTestCase;

/**
 * User manager unit tests.
 */
class UserManagerTest extends UserTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFindNotFoundException()
    {
        $this->setExpectedException(NotFoundException::class);

        static::$userManager->find(
            new ValueObject([
                'username' => 'i_dont_exist_3y437824y',
            ]),
            true
        );
    }

    public function testInvalidClassOnCreate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userManager->create($object);
    }

    public function testInvalidClassOnUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userManager->update($object);
    }

    public function testInvalidClassOnCreateOrUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userManager->createOrUpdate($object);
    }

    public function testInvalidClassOnDelete()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$userManager->remove($object);
    }
}
