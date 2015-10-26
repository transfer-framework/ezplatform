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
 * Updater interface.
 */
interface UpdaterInterface
{
    /**
     * Updates an object.
     *
     * @param ObjectInterface $object Object to update
     *
     * @return ObjectInterface|null
     */
    public function update(ObjectInterface $object);

    /**
     * Updates or creates an object.
     *
     * @param ObjectInterface $object Object to update or create
     *
     * @return ObjectInterface|null
     */
    public function createOrUpdate(ObjectInterface $object);
}
