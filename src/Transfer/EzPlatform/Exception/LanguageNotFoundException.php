<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
namespace Transfer\EzPlatform\Exception;

use eZ\Publish\API\Repository\Exceptions\NotFoundException;

/**
 * Exception class for languages which cannot be found.
 *
 * @author Harald Tollefsen <harald@netmaking.no>
 */
class LanguageNotFoundException extends NotFoundException
{
}
