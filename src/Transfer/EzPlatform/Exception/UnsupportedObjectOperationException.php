<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Exception;

class UnsupportedObjectOperationException extends \Exception
{
    /**
     * UnsupportedObjectOperationException constructor.
     *
     * @param string $expectedObject
     * @param string $actualObject
     */
    public function __construct($expectedObject, $actualObject)
    {
        parent::__construct(sprintf('Expected "%s", got "%s".', $expectedObject, $actualObject));
    }
}
