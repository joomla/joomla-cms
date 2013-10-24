<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */


if (version_compare(PHP_VERSION, '5.3.1', '<'))
{
	die('Your host needs to use PHP 5.3.1 or higher to run this version of Joomla!');
}

// This is a valid Joomla! entry point.
define('_JEXEC', 1);
define('JAPPLICATION_CONFIG', dirname(__DIR__) . '/configuration.php');

// TODO - Try and live without JPATH_BASE
define('JPATH_BASE', __DIR__);

// This file must retain PHP 5.2 compatible syntax, so hide any namespacing, etc, in the bootstrap.php file.
require __DIR__ . '/src/bootstrap.php';
