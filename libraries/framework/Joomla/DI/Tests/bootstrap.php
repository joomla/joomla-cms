<?php
/**
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// Search for the Composer autoload file
$composerAutoload = __DIR__ . '/../../../../../autoload.php';

if (file_exists($composerAutoload))
{
	include_once $composerAutoload;
}
else
// We're not installed via composer, so load our own autoloader.
{
	include_once __DIR__ . '/../../../../tests/bootstrap.php';
}
