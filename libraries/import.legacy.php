<?php
/**
 * Bootstrap file for the Joomla Platform [with legacy libraries].  Including this file into your application
 * will make Joomla Platform libraries [including legacy libraries] available for use.
 *
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE
 */

// Set the platform root path as a constant if necessary.
if (!defined('JPATH_PLATFORM'))
{
	define('JPATH_PLATFORM', __DIR__);
}

// Detect the native operating system type.
$os = strtoupper(substr(PHP_OS, 0, 3));

if (!defined('IS_WIN'))
{
	define('IS_WIN', ($os === 'WIN') ? true : false);
}

if (!defined('IS_UNIX'))
{
	define('IS_UNIX', (($os !== 'MAC') && ($os !== 'WIN')) ? true : false);
}

/**
 * @deprecated 13.3	Use IS_UNIX instead
 */
if (!defined('IS_MAC'))
{
	define('IS_MAC', (IS_UNIX === true && ($os === 'DAR' || $os === 'MAC')) ? true : false);
}

// Import the library loader if necessary.
if (!class_exists('JLoader'))
{
	require_once JPATH_PLATFORM . '/loader.php';
}

// Make sure that the Joomla Loader has been successfully loaded.
if (!class_exists('JLoader'))
{
	throw new RuntimeException('Joomla Loader not loaded.');
}

// Setup the autoloaders.
JLoader::setup();

JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');

// Register classes that don't follow one file per class naming conventions.
JLoader::register('JText', JPATH_PLATFORM . '/joomla/language/text.php');
JLoader::register('JRoute', JPATH_PLATFORM . '/joomla/application/route.php');

// Check if the JsonSerializable interface exists already
if (!interface_exists('JsonSerializable'))
{
	JLoader::register('JsonSerializable', JPATH_PLATFORM . '/vendor/joomla/compat/src/JsonSerializable.php');
}

// Add deprecated constants
// @deprecated 4.0
define('JPATH_ISWIN', IS_WIN);
define('JPATH_ISMAC', IS_MAC);

// Register the PasswordHash lib
JLoader::register('PasswordHash', JPATH_PLATFORM . '/phpass/PasswordHash.php');

// Register classes where the names have been changed to fit the autoloader rules
// @deprecated  4.0
JLoader::register('JSimpleCrypt', JPATH_PLATFORM . '/legacy/simplecrypt/simplecrypt.php');
JLoader::register('JTree', JPATH_PLATFORM . '/legacy/base/tree.php');
JLoader::register('JNode', JPATH_PLATFORM . '/legacy/base/node.php');
JLoader::register('JObserver', JPATH_PLATFORM . '/legacy/base/observer.php');
JLoader::register('JObservable', JPATH_PLATFORM . '/legacy/base/observable.php');
JLoader::register('LogException', JPATH_PLATFORM . '/legacy/log/logexception.php');
JLoader::register('JXMLElement', JPATH_PLATFORM . '/legacy/utilities/xmlelement.php');
JLoader::register('JCli', JPATH_PLATFORM . '/legacy/application/cli.php');
JLoader::register('JDaemon', JPATH_PLATFORM . '/legacy/application/daemon.php');
JLoader::register('JApplication', JPATH_LIBRARIES . '/legacy/application/application.php');
