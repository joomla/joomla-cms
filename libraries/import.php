<?php
/**
 * @package     Joomla.Platform
 *
 * @copyright   Copyright (C) 2005 - 2011 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', dirname(__FILE__));
}

// Set the directory separator define if necessary.
if (!defined('DS'))
{
	define('DS', DIRECTORY_SEPARATOR);
}

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));
if (!defined('IS_WIN'))
{
	define('IS_WIN', ($os === 'WIN') ? true : false);
}
if (!defined('IS_MAC'))
{
	define('IS_MAC', ($os === 'MAC') ? true : false);
}
if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);
}

// Import the platform version library if necessary.
if (!class_exists('JPlatform'))
{
	require_once JPATH_PLATFORM . '/platform.php';
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

/**
 * Import the base Joomla Platform libraries.
 */

// Import the factory library.
JLoader::import('joomla.factory');

// Import the exception and error handling libraries.
JLoader::import('joomla.error.error');
JLoader::import('joomla.error.exception');

/*
 * If the HTTP_HOST environment variable is set we assume a Web request and
 * thus we import the request library and most likely clean the request input.
 */
if (isset($_SERVER['HTTP_HOST']))
{
	JLoader::import('joomla.environment.request');

	// If an application flags it doesn't want this, adhere to that.
	if (!defined('_JREQUEST_NO_CLEAN'))
	{
		JRequest::clean();
	}
}

// Import the base object library.
JLoader::import('joomla.base.object');

// Register classes that don't follow one file per class naming conventions.
JLoader::register('JText', JPATH_PLATFORM . '/joomla/methods.php');
JLoader::register('JRoute', JPATH_PLATFORM . '/joomla/methods.php');
