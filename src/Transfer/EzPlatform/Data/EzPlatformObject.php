<?php

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\Action\ActionInterface;
use Transfer\EzPlatform\Data\Action\Enum\Action;

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
