<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Repository\Manager\Type;

use Transfer\Data\ObjectInterface;

/**
 * Creator interface.
 *
 * @internal
 */
interface CreatorInterface
{
    /**
     * Creates an object.
     *
     * @param ObjectInterface $object Object to create
     *
     * @return ObjectInterface|null
     */
    public function create(ObjectInterface $object);
}
