<?php
/**
 * @package    Joomla.Installation
 *
 * @copyright  Copyright (C) 2005 - 2013 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// This file must retain PHP 5.2 compatible syntax.

if (version_compare(PHP_VERSION, '5.3.1', '<'))
{
	die('Your host needs to use PHP 5.3.1 or higher to run this version of Joomla!');
}

// This is a valid Joomla! entry point.
define('_JEXEC', 1);

// Bootstrap the app
require_once __DIR__ . '/src/bootstrap.php';
