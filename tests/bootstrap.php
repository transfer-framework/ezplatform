<?php

/*
 * This file is part of Transfer.
 *
 * For the full copyright and license information, please view the LICENSE file located
 * in the root directory.
 */

$configDir = __DIR__.'/../vendor/ezsystems/ezpublish-kernel';

if (!file_exists($configDir.'/config.php')) {
    if (!symlink($configDir.'/config.php-DEVELOPMENT', $configDir.'/config.php')) {
        throw new \RuntimeException('Could not symlink config.php-DEVELOPMENT to config.php!');
    }
}

/** @var Composer\Autoload\ClassLoader $loader */
$loader = require __DIR__.'/../vendor/autoload.php';
//$loader->addPsr4('Transfer\\EzPlatform\\', __DIR__.'/../src/Transfer/EzPlatform');


//$loader->addPsr4('Transfer\\EzPlatform\\tests\\unit\\', __DIR__ . '/unit/Transfer/EzPlatform');
//$loader->addPsr4('Transfer\\EzPlatform\\tests\\testcase\\', __DIR__ . '/tests/testcase');
//$loader->addPsr4('Transfer\\EzPlatform\\tests\\integration\\', __DIR__ . '/tests/integration');
