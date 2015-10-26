<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Exception;

/**
 * Exception class for cases when object data is malformed.
 */
class MalformedObjectDataException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Malformed object data');
    }
}
