<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Type;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Exception\ObjectNotFoundException;

/**
 * Finder interface.
 *
 * @internal
 */
interface FinderInterface
{
    /**
     * Find an eZ Object, based on a Transfer object.
     *
     * @param ValueObject $object
     *
     * @return \eZ\Publish\API\Repository\Values\ValueObject
     *
     * @throws ObjectNotFoundException
     */
    public function find(ValueObject $object);
}
