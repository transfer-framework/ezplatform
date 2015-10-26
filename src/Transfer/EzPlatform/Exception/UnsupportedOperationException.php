<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Exception;

/**
 * Exception class for cases when an operation is not supported.
 */
class UnsupportedOperationException extends \Exception
{
    /**
     * @param string $message Message
     */
    public function __construct($message)
    {
        parent::__construct($message);
    }
}
