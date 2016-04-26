<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Data\Action;

/**
 * Action interface.
 *
 * @internal
 */
interface ActionInterface
{
    /**
     * @return int {@link see \Transfer\EzPlatform\Data\Action\Enum\Action}
     */
    public function getAction();
}
