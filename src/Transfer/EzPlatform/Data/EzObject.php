<?php

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;
use Transfer\EzPlatform\Data\Enum\Action;

abstract class EzObject extends ValueObject
{
    public function getAction()
    {
        return $this->getProperty('action') ?: Action::CREATEORUPDATE;
    }
}