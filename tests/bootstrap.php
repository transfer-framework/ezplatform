<?php

/**
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */
error_reporting(E_ALL);

$configDir = __DIR__.'/../vendor/ezsystems/ezpublish-kernel';

if (!file_exists($configDir.'/config.php')) {
    if (!symlink($configDir.'/config.php-DEVELOPMENT', $configDir.'/config.php')) {
        throw new \RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php!');
    }
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
