<?php
/**
 * @package    Joomla.Platform
 *
 * @copyright  Copyright (C) 2005 - 2012 Open Source Matters, Inc. All rights reserved.
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

class_exists('JLoader') or die;

// Setup the autoloaders.
JLoader::setup();

// Register the legacy library base path for deprecated or legacy libraries.
JLoader::registerPrefix('J', JPATH_PLATFORM . '/legacy');

// Import the Joomla Factory.
JLoader::import('joomla.factory');

// Register classes that don't follow one file per class naming conventions.
JLoader::register('JText', JPATH_PLATFORM . '/joomla/language/text.php');
JLoader::register('JRoute', JPATH_PLATFORM . '/joomla/application/route.php');

// Register the folder for the moved JHtml classes
JHtml::addIncludePath(JPATH_PLATFORM . '/legacy/html');

// Register classes for compatability with PHP 5.3
if (version_compare(PHP_VERSION, '5.4.0', '<'))
{
	JLoader::register('JsonSerializable', __DIR__ . '/compat/jsonserializable.php');
}

// Add deprecated constants
// @deprecated 12.3
define('JPATH_ISWIN', IS_WIN);
define('JPATH_ISMAC', IS_MAC);

// Register classes where the names have been changed to fit the autoloader rules
// @deprecated  12.3
JLoader::register('JSimpleCrypt', JPATH_PLATFORM . '/legacy/simplecrypt/simplecrypt.php');
JLoader::register('JTree', JPATH_PLATFORM . '/legacy/base/tree.php');
JLoader::register('JNode', JPATH_PLATFORM . '/legacy/base/node.php');
JLoader::register('JObserver', JPATH_PLATFORM . '/legacy/base/observer.php');
JLoader::register('JObservable', JPATH_PLATFORM . '/legacy/base/observable.php');
JLoader::register('LogException', JPATH_PLATFORM . '/legacy/log/logexception.php');
JLoader::register('JXMLElement', JPATH_PLATFORM . '/legacy/utilities/xmlelement.php');
JLoader::register('JRule', JPATH_PLATFORM . '/legacy/access/rule.php');
JLoader::register('JRules', JPATH_PLATFORM . '/legacy/access/rules.php');
JLoader::register('JCli', JPATH_PLATFORM . '/legacy/application/cli.php');
JLoader::register('JDaemon', JPATH_PLATFORM . '/legacy/application/daemon.php');
