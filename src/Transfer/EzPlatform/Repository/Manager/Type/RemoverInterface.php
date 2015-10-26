<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Manager\Type;

use Transfer\Data\ObjectInterface;

/**
 * Remover interface.
 */
interface RemoverInterface
{
    /**
     * Removes an object.
     *
     * @param ObjectInterface $object Object to remove
     *
     * @return bool
     */
    public function remove(ObjectInterface $object);
}
