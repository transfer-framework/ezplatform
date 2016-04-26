<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Type;

use Transfer\Data\ValueObject;

/**
 * Finder interface.
 *
 * @internal
 */
interface FinderInterface
{
    /**
     * Find an object.
     *
     * @param ValueObject $object Object to find
     *
     * @param bool $throwException
     *
     * @return ValueObject|false
     */
    public function find(ValueObject $object, $throwException);
}
