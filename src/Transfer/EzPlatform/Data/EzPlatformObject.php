<?php

namespace Transfer\EzPlatform\Data;

use Transfer\Data\ValueObject;

use Transfer\EzPlatform\Data\Enum\Action;
use Transfer\EzPlatform\Repository\Manager\Type\CreatorInterface;

abstract class EzPlatformObject extends ValueObject
{
    public function getAction()
    {
        return $this->getProperty('action') ?: Action::CREATEORUPDATE;
    }
}