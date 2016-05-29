<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

namespace Transfer\EzPlatform\Exception;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Exception class for cases when.
 */
class ObjectNotFoundException extends NotFoundException
{
    /**
     * ObjectNotFoundException constructor.
     *
     * @param string   $class
     * @param string[] $identifiers
     */
    public function __construct($class, array $identifiers)
    {
        parent::__construct(sprintf(
            '%s not found. Checked with identifier(s): %s.',
            $class,
            implode('", "', $identifiers)
        ));
    }
}
