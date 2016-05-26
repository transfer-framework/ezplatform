<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Tests\Repository\Manager;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\Values\Content\Location;
use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\UnsupportedObjectOperationException;
use Transfer\EzPlatform\tests\testcase\LocationTestCase;

/**
 * Location manager unit tests.
 */
class LocationManagerTest extends LocationTestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testFindNotFoundException()
    {
        $this->setExpectedException(NotFoundException::class);

        static::$locationManager->find(
            new ValueObject([
                'remote_id' => 'i_dont_exist_321123',
            ]),
            true
        );
    }

    public function testToggleVisibility()
    {
        $location = static::$locationManager->find(new ValueObject([], ['id' => 2]));

        $hidden = $location->hidden;
        $location = static::$locationManager->toggleVisibility($location);
        $this->assertEquals(!$hidden, $location->hidden);

        $hidden = !$hidden;
        $location = static::$locationManager->toggleVisibility($location);
        $this->assertEquals(!$hidden, $location->hidden);

    }

    public function testInvalidClassOnCreate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$locationManager->create($object);
    }

    public function testInvalidClassOnUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$locationManager->update($object);
    }

    public function testInvalidClassOnCreateOrUpdate()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$locationManager->createOrUpdate($object);
    }

    public function testInvalidClassOnDelete()
    {
        $this->setExpectedException(UnsupportedObjectOperationException::class);

        $object = new ValueObject([]);
        static::$locationManager->remove($object);
    }
}
