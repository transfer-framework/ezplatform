<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Type;

use eZ\Publish\API\Repository\Values\ValueObject as EzValueObject;
use Transfer\Data\ValueObject as TransferValueObject;
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
     * @param TransferValueObject $object
     *
     * @return EzValueObject
     *
     * @throws ObjectNotFoundException
     */
    public function find(TransferValueObject $object);
}
