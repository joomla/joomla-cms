<?php
/**
 * @package    Joomla.Libraries
 *
 * @copyright  Copyright (C) 2005 - 2019 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('_JEXEC') or die;

trigger_error(
	sprintf(
		'Bootstrapping Joomla using the %1$s file is deprecated.  Use %2$s instead.',
		__FILE__,
		__DIR__ . '/bootstrap.php'
	),
	E_USER_DEPRECATED
);

// Set the platform root path as a constant if necessary
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', __DIR__);
}

// Import the library loader if necessary
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Platform has been successfully loaded
if (!class_exists('JLoader'))
{
	throw new RuntimeException('Joomla Platform not loaded.');
}

// Register the library base path for CMS libraries
JLoader::registerPrefix('J', JPATH_PLATFORM . '/cms', false, true);

// Create the Composer autoloader
$loader = require JPATH_LIBRARIES . '/vendor/autoload.php';

// We need to pull our decorated class loader into memory before unregistering Composer's loader
class_exists('\\Joomla\\CMS\\Autoload\\ClassLoader');

$loader->unregister();

// Decorate Composer autoloader
spl_autoload_register(array(new \Joomla\CMS\Autoload\ClassLoader($loader), 'loadClass'), true, true);

// Register the class aliases for Framework classes that have replaced their Platform equivalents
require_once JPATH_LIBRARIES . '/classmap.php';

// Register a handler for uncaught exceptions that shows a pretty error page when possible
set_exception_handler(array('JErrorPage', 'render'));

// Define the Joomla version if not already defined
if (!defined('JVERSION'))
{
	$jversion = new JVersion;
	define('JVERSION', $jversion->getShortVersion());
}

// Set up the message queue logger for web requests
if (array_key_exists('REQUEST_METHOD', $_SERVER))
{
	JLog::addLogger(array('logger' => 'messagequeue'), JLog::ALL, array('jerror'));
}

// Register the Crypto lib
JLoader::register('Crypto', JPATH_PLATFORM . '/php-encryption/Crypto.php');
