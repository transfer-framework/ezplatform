<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Exception;

use Transfer\Data\ValueObject;

/**
 * Exception class for cases when identification property is missing.
 */
class MissingIdentificationPropertyException extends \Exception
{
    /**
     * @param ValueObject $object
     */
    public function __construct(ValueObject $object)
    {
        parent::__construct(sprintf(
            'Could not find identification property for "%s". Use `update` or `create` manager methods instead.',
            $object->getProperty('name')
        ));
    }
}
