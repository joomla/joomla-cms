<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

defined('_JEXEC') or die;

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM')) {
	define('JPATH_PLATFORM', __DIR__);
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

class_exists('JLoader') or die;

// Register the library base path for CMS libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms');

// Register a handler for uncaught exceptions that shows a pretty error page when possible
set_exception_handler(array('JErrorPage', 'render'));

// Define the Joomla version if not already defined.
if (!defined('JVERSION')) {
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(array('logger' => 'messagequeue'), JLog::ALL, array('jerror'));
}

// Register classes where the names have been changed to fit the autoloader rules
// @deprecated  4.0
JLoader::register('JToolBar', JPATH_PLATFORM . '/cms/toolbar/toolbar.php');
JLoader::register('JButton',  JPATH_PLATFORM . '/cms/toolbar/button.php');
