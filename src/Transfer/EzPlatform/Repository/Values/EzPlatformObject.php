<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Repository\Values;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Repository\Values\Action\ActionInterface;
use Transfer\EzPlatform\Repository\Values\Action\Enum\Action;

abstract class EzPlatformObject extends ValueObject implements ActionInterface
{
    /**
     * {@inheritdoc}
     */
    public function getAction()
    {
        return $this->getProperty('action') ?: Action::CREATEORUPDATE;
    }
}
